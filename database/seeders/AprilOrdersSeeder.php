<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ImportItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AprilOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::create(2026, 4, 1)->startOfDay();
        $end = Carbon::now()->startOfDay();

        $this->command->info("Creating orders from {$start->toDateString()} to {$end->toDateString()}");

        $customersApril = Customer::whereBetween('created_at', [Carbon::create(2026, 4, 1), Carbon::create(2026, 4, 30)->endOfDay()])->get();
        $customersMarch = Customer::whereBetween('created_at', [Carbon::create(2026, 3, 1), Carbon::create(2026, 3, 31)->endOfDay()])->get();

        if ($customersApril->isEmpty()) {
            $this->command->error('No customers created in April found. Seeding aborted.');
            return;
        }

        $variants = ImportItem::select('product_variant_id')->distinct()->pluck('product_variant_id')->toArray();
        if (empty($variants)) {
            $this->command->error('No product variants with import items found. Aborted.');
            return;
        }

        $paymentMethods = ['COD', 'VNPAY', 'MOMO'];

        $date = $start->copy();
        $shortages = [];
        $totalCreated = 0;

        while ($date <= $end) {
            $targetPerDay = 5; // ensure at least 5 orders/day
            $createdToday = 0;

            for ($i = 0; $i < $targetPerDay; $i++) {
                // choose customer: 90% April customers, 10% March
                $useApril = (rand(1, 100) <= 90) && $customersApril->isNotEmpty();
                $customer = $useApril ? $customersApril->random() : ($customersMarch->isNotEmpty() ? $customersMarch->random() : $customersApril->random());

                // pick payment
                $paymentMethod = $paymentMethods[($i + $date->day) % count($paymentMethods)];

                // create order with placeholder total (compute after items)
                $orderedAt = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'receiver_name' => $customer->user->name ?? ($customer->name ?? 'Khách hàng'),
                    'receiver_phone' => $customer->phone,
                    'shipping_address' => $customer->address,
                    'note' => null,
                    'total_amount' => 0,
                    'status' => 'pending',
                    'shipping_fee' => rand(0, 1) ? 30000 : 20000,
                    'discount_amount' => 0,
                    'discount_code' => null,
                    'created_at' => $orderedAt,
                    'updated_at' => $orderedAt,
                ]);

                // add 1-2 items
                $itemCount = rand(1, 2);
                $itemsAdded = 0;
                $totalAmount = 0;

                // shuffle available variants
                $availableVariants = $variants;
                shuffle($availableVariants);

                foreach ($availableVariants as $pvId) {
                    if ($itemsAdded >= $itemCount) break;

                    // attempt to allocate from import_items FIFO
                    $needed = rand(1, 2);
                    $importBatches = ImportItem::where('product_variant_id', $pvId)->where('remaining_quantity', '>', 0)->orderBy('created_at', 'asc')->get();
                    if ($importBatches->isEmpty()) continue;

                    $actualQty = 0;
                    $batchDetails = [];
                    foreach ($importBatches as $batch) {
                        if ($needed <= 0) break;
                        $use = min($needed, $batch->remaining_quantity);
                        if ($use <= 0) continue;
                        // decrement
                        $batch->decrement('remaining_quantity', $use);
                        $actualQty += $use;
                        $batchDetails[] = ['batch_id' => $batch->id, 'quantity' => $use, 'unit_price' => $batch->unit_price];
                        $needed -= $use;
                    }

                    if ($actualQty <= 0) continue;

                    // set price from first import batch unit_price or fallback
                    $price = $batchDetails[0]['unit_price'] ?? rand(50000, 200000);
                    $subtotal = $price * $actualQty;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $pvId,
                        'quantity' => $actualQty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'cost_price' => $price * 0.7,
                        'batch_details' => json_encode($batchDetails),
                    ]);

                    $totalAmount += $subtotal;
                    $itemsAdded++;
                }

                if ($itemsAdded === 0) {
                    // couldn't create items due to lack of stock
                    $order->delete();
                    $shortages[] = ['date' => $date->toDateString(), 'reason' => 'no_stock'];
                    continue;
                }

                // finalize totals
                $totalAmount += $order->shipping_fee;
                $order->update(['total_amount' => $totalAmount]);

                // create payment
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'method' => $paymentMethod,
                    'amount' => $totalAmount,
                    'status' => 'paid',
                    'transaction_code' => ($paymentMethod === 'COD') ? null : strtoupper($paymentMethod) . 'TX' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'created_at' => $orderedAt->copy()->addHours(rand(1, 48)),
                    'updated_at' => $orderedAt->copy()->addHours(rand(1, 48)),
                    'paid_at' => $orderedAt->copy()->addHours(rand(1, 48)),
                ]);

                // decide status flow
                $rand = rand(1, 100);
                if ($rand <= 8) {
                    // decide status flow and build timeline
                    $rand = rand(1, 100);

                    // default timelines
                    $receivedAt = $orderedAt->copy()->addDays(rand(3, 5));
                    $firstTransition = $receivedAt->copy()->addDays(rand(1, 2));
                    $secondTransition = $firstTransition->copy()->addDays(rand(1, 2));

                    if ($rand <= 8) {
                        // cancelled early: cancel 0-2 days after order
                        $cancelAt = $orderedAt->copy()->addDays(rand(0, 2));
                        // do NOT add new columns; use existing fields only
                        $order->update(['status' => 'cancelled', 'updated_at' => $cancelAt]);
                        DB::table('order_cancellations')->insert([
                            'order_id' => $order->id,
                            'reason' => 'Khách hủy/không nhận',
                            'cancelled_by' => 'customer',
                            'cancelled_at' => $cancelAt,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        // refund if paid: use refund fields if present
                        $refundData = [];
                        if (Schema::hasColumn('payments', 'refund_status')) $refundData['refund_status'] = 'completed';
                        if (Schema::hasColumn('payments', 'refund_amount')) $refundData['refund_amount'] = $payment->amount;
                        if (Schema::hasColumn('payments', 'refund_at')) $refundData['refund_at'] = now();
                        if (!empty($refundData)) $payment->update($refundData);
                        // restore batches (we already decremented above)
                        $this->restoreBatchesForOrder($order);
                    } elseif ($rand <= 8 + 10) {
                        // return case (10%): customer returned after receiving
                        // record status and set updated_at as timeline marker (no new columns)
                        $order->update(['status' => 'refunded', 'updated_at' => $secondTransition]);

                        $returnData = [
                            'order_id' => $order->id,
                            'reason' => 'wrong_product',
                            'description' => 'Khách nhận nhầm',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $returnId = DB::table('order_returns')->insertGetId($returnData);

                        DB::table('order_return_images')->insert([
                            'order_return_id' => $returnId,
                            'image_path' => 'returns/sample_return.jpg',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // restore batches for this order -> returned to stock
                        $this->restoreBatchesForOrder($order, true);
                        // refund payment via refund fields if present
                        $refundData = [];
                        if (Schema::hasColumn('payments', 'refund_status')) $refundData['refund_status'] = 'completed';
                        if (Schema::hasColumn('payments', 'refund_amount')) $refundData['refund_amount'] = $payment->amount;
                        if (Schema::hasColumn('payments', 'refund_at')) $refundData['refund_at'] = now();
                        if (!empty($refundData)) $payment->update($refundData);
                    } elseif ($rand <= 100 - 2) {
                        // completed (~80%): received then completed
                        $order->update(['status' => 'completed', 'updated_at' => $secondTransition]);
                    } else {
                        // shipping (~2%): shipped after received
                        $order->update(['status' => 'shipping', 'updated_at' => $firstTransition]);
                    }
                    // shipping (~2%)
                    $order->update(['status' => 'shipping']);
                }

                $createdToday++;
                $totalCreated++;
            }

            $date->addDay();
        }

        $this->command->info("Total orders created: {$totalCreated}");

        if (!empty($shortages)) {
            $this->command->warn('Shortages detected on some dates.');
            foreach ($shortages as $s) {
                $this->command->warn(json_encode($s));
            }
        }
    }

    private function restoreBatchesForOrder(Order $order, $isReturn = false)
    {
        foreach ($order->order_items as $item) {
            $batchDetails = $item->batch_details;
            if (is_string($batchDetails) && $batchDetails !== '') {
                $decoded = json_decode($batchDetails, true);
                if (json_last_error() === JSON_ERROR_NONE) $batchDetails = $decoded;
            }
            if (is_array($batchDetails) && !empty($batchDetails)) {
                foreach ($batchDetails as $b) {
                    if (!isset($b['batch_id'], $b['quantity'])) continue;
                    $import = ImportItem::lockForUpdate()->find($b['batch_id']);
                    if (!$import) continue;
                    if ($isReturn) {
                        // returned good -> add back
                        $import->increment('remaining_quantity', intval($b['quantity']));
                    } else {
                        // cancellation -> add back but not exceed quantity
                        $allowed = max(0, intval($import->quantity) - intval($import->remaining_quantity));
                        $inc = min($allowed, intval($b['quantity']));
                        if ($inc > 0) $import->increment('remaining_quantity', $inc);
                    }
                }
            }
        }
    }
}
