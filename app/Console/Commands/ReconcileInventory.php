<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileInventory extends Command
{
    protected $signature = 'fix:reconcile-inventory';
    protected $description = 'Reconcile inventory table to match calculated stock (imports - sales)';

    public function handle()
    {
        $this->info('Đối soát tồn kho');
        $this->line(str_repeat('=', 140));

        // Get all variants
        $variants = DB::table('product_variants')->get();
        $fixes = [];

        echo "\nRecalculating stock for all variants:\n";
        echo str_repeat('-', 140) . "\n";

        $totalMismatch = 0;
        $variantsFix = 0;

        foreach ($variants as $v) {
            $imported = DB::table('import_items')
                ->where('product_variant_id', $v->id)
                ->sum('quantity') ?? 0;

            $sold = DB::table('order_items')
                ->where('product_variant_id', $v->id)
                ->sum('quantity') ?? 0;

            $calculated = $imported - $sold;

            $current = DB::table('inventories')
                ->where('product_variant_id', $v->id)
                ->value('quantity') ?? 0;

            if ($calculated != $current) {
                $diff = $current - $calculated;
                $totalMismatch += abs($diff);
                $variantsFix++;

                $fixes[] = [
                    'variant_id' => $v->id,
                    'sku' => $v->sku,
                    'calculated' => $calculated,
                    'current' => $current,
                    'diff' => $diff,
                ];

                // Update inventory
                DB::table('inventories')
                    ->updateOrInsert(
                        ['product_variant_id' => $v->id],
                        ['quantity' => max(0, $calculated), 'updated_at' => now()]
                    );
            }
        }

        echo sprintf("Updated %d variants\n", $variantsFix);
        echo sprintf("Total unit corrections: %d\n", $totalMismatch);

        if (count($fixes) > 0) {
            echo "\n📝 Changes made:\n";
            echo str_repeat('-', 140) . "\n";
            foreach (array_slice($fixes, 0, 20) as $f) {
                echo sprintf(
                    "Variant #%d (%s): %d → %d (was: %d, diff: %+d)\n",
                    $f['variant_id'],
                    substr($f['sku'], 0, 30),
                    $f['current'],
                    $f['calculated'],
                    $f['current'],
                    $f['diff']
                );
            }
            if (count($fixes) > 20) {
                echo sprintf("... and %d more variants\n", count($fixes) - 20);
            }
        }

        // Kiểm tra lại tổng tồn sau khi cập nhật
        echo "\n" . str_repeat('=', 140) . "\n";
        echo "Kiểm tra sau khi cập nhật:\n";

        $totalImported = DB::table('import_items')->sum('quantity') ?? 0;
        $totalSold = DB::table('order_items')->sum('quantity') ?? 0;
        $calculated = $totalImported - $totalSold;
        $inventoryTable = DB::table('inventories')->sum('quantity') ?? 0;

        printf("Total Imported: %d\n", $totalImported);
        printf("Total Sold: %d\n", $totalSold);
        printf("Calculated: %d\n", $calculated);
        printf("Inventory Table: %d\n", $inventoryTable);

        if ($calculated == $inventoryTable) {
            echo "Kiểm tra thành công: tồn kho đã khớp.\n";
        } else {
            echo sprintf("Chênh lệch: %+d\n", $inventoryTable - $calculated);
        }
    }
}
