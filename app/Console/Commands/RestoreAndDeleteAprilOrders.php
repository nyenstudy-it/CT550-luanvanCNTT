<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\ImportItem;
use App\Models\Inventory;

class RestoreAndDeleteAprilOrders extends Command
{
    protected $signature = 'app:restore-delete-april {--start=2026-04-01} {--end=2026-04-18}';

    protected $description = 'Restore import_items.remaining_quantity from order_items.batch_details for orders in date range, then delete those orders and dependents.';

    public function handle(): int
    {
        $start = Carbon::parse($this->option('start'))->startOfDay();
        $end = Carbon::parse($this->option('end'))->endOfDay();

        $this->info("Start: {$start}  End: {$end}");

        DB::beginTransaction();

        try {
            $this->info('Collecting orders...');

            Order::whereBetween('created_at', [$start, $end])
                ->chunkById(200, function ($orders) {
                    foreach ($orders as $order) {
                        foreach ($order->order_items as $item) {
                            $batchDetails = $item->batch_details;

                            if (is_string($batchDetails) && $batchDetails !== '') {
                                $decoded = json_decode($batchDetails, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $batchDetails = $decoded;
                                }
                            }

                            if (is_array($batchDetails) && !empty($batchDetails)) {
                                foreach ($batchDetails as $b) {
                                    if (!isset($b['batch_id'])) continue;
                                    $qty = isset($b['quantity']) ? intval($b['quantity']) : intval($item->quantity);
                                    $import = ImportItem::lockForUpdate()->find($b['batch_id']);
                                    if ($import) {
                                        $allowed = max(0, intval($import->quantity) - intval($import->remaining_quantity));
                                        $inc = min($allowed, $qty);
                                        if ($inc > 0) {
                                            $import->increment('remaining_quantity', $inc);
                                        }
                                    }
                                }
                                continue;
                            }

                            // fallback: if order_item has batch_id column
                            if (isset($item->batch_id) && $item->batch_id) {
                                $import = ImportItem::lockForUpdate()->find($item->batch_id);
                                if ($import) {
                                    $allowed = max(0, intval($import->quantity) - intval($import->remaining_quantity));
                                    $inc = min($allowed, intval($item->quantity));
                                    if ($inc > 0) {
                                        $import->increment('remaining_quantity', $inc);
                                    }
                                }
                                continue;
                            }

                            // If no batch info, best-effort: try to add back to inventories via product_variant
                            if (isset($item->product_variant_id) && $item->product_variant_id) {
                                // create or update inventory available_quantity
                                Inventory::updateOrCreate(
                                    ['product_variant_id' => $item->product_variant_id],
                                    ['available_quantity' => DB::raw('available_quantity + ' . intval($item->quantity))]
                                );
                            }
                        }
                    }
                });

            $this->info('Recomputing inventories from import_items...');
            // Recompute inventories totals
            $rows = DB::table('import_items')
                ->select('product_variant_id', DB::raw('SUM(remaining_quantity) as available_qty'), DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('product_variant_id')
                ->get();

            foreach ($rows as $r) {
                Inventory::updateOrCreate(
                    ['product_variant_id' => $r->product_variant_id],
                    [
                        'available_quantity' => $r->available_qty,
                        'total_quantity' => $r->total_qty,
                        'updated_at' => now(),
                    ]
                );
            }

            $this->info('Deleting dependent records...');

            // order_return_images -> order_returns
            DB::table('order_return_images')
                ->whereIn('order_return_id', function ($q) use ($start, $end) {
                    $q->select('id')->from('order_returns')->whereIn('order_id', function ($q2) use ($start, $end) {
                        $q2->select('id')->from('orders')->whereBetween('created_at', [$start, $end]);
                    });
                })->delete();

            DB::table('order_returns')
                ->whereIn('order_id', function ($q) use ($start, $end) {
                    $q->select('id')->from('orders')->whereBetween('created_at', [$start, $end]);
                })->delete();

            DB::table('order_cancellations')
                ->whereIn('order_id', function ($q) use ($start, $end) {
                    $q->select('id')->from('orders')->whereBetween('created_at', [$start, $end]);
                })->delete();

            DB::table('payments')
                ->whereIn('order_id', function ($q) use ($start, $end) {
                    $q->select('id')->from('orders')->whereBetween('created_at', [$start, $end]);
                })->delete();

            DB::table('order_items')
                ->whereIn('order_id', function ($q) use ($start, $end) {
                    $q->select('id')->from('orders')->whereBetween('created_at', [$start, $end]);
                })->delete();

            DB::table('orders')->whereBetween('created_at', [$start, $end])->delete();

            DB::commit();

            $this->info('Completed: restored stock and deleted April orders.');
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
