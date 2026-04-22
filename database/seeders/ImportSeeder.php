<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportItem;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ImportSeeder extends Seeder
{
    /**
     * Tạo imports tháng 3/2026 - Cân bằng tất cả 79 sản phẩm
     * Mỗi sản phẩm được nhập 15-35 units
     */
    public function run(): void
    {
        // Kiểm tra đã có import tháng 3/2026 chưa
        $existingImports = Import::whereBetween('import_date', ['2026-03-01', '2026-03-31'])->count();

        if ($existingImports > 0) {
            $this->command->info("ℹ️  Đã có {$existingImports} imports tháng 3/2026. Skip seeder.");
            return;
        }

        // Lấy suppliers
        $suppliers = Supplier::limit(5)->get();
        if ($suppliers->isEmpty()) {
            $this->command->error('❌ Chưa có suppliers!');
            return;
        }

        // Lấy product variants
        $variants = ProductVariant::all()->shuffle();
        if ($variants->isEmpty()) {
            $this->command->error('❌ Chưa có product variants!');
            return;
        }

        $this->command->info("📦 Tạo imports tháng 3/2026 - Cân bằng 79 sản phẩm...");

        // === BƯỚC 1: PHÂN CHIA 79 SẢN PHẨM ĐỀU CHO 4 IMPORTS ===
        // Mỗi import: 79/4 ≈ 20 sản phẩm
        $productsPerImport = ceil($variants->count() / 4);

        // === BƯỚC 2: TẠO 4 IMPORTS ===
        $importDates = [
            Carbon::create(2026, 3, 2),
            Carbon::create(2026, 3, 10),
            Carbon::create(2026, 3, 18),
            Carbon::create(2026, 3, 26),
        ];

        $variantIndex = 0;

        foreach ($importDates as $importDate) {
            $supplier = $suppliers->random();

            $import = Import::create([
                'supplier_id' => $supplier->id,
                'import_date' => $importDate,
                'total_amount' => 0,  // Tính lại sau
                'staff_id' => 1,  // Admin
            ]);

            $totalAmount = 0;
            $itemsInImport = 0;

            // Lấy products cho import này
            $endIdx = min($variantIndex + $productsPerImport, $variants->count());

            for ($i = $variantIndex; $i < $endIdx; $i++) {
                $variant = $variants[$i];

                // Mỗi sản phẩm: 15-35 units (để đủ cho bán và dư)
                $quantity = fake()->numberBetween(15, 35);

                // Unit price: 50-80% của retail price
                $unitPrice = $variant->price
                    ? $variant->price * fake()->randomFloat(2, 0.50, 0.80)
                    : fake()->numberBetween(50000, 300000);

                $itemTotal = $quantity * $unitPrice;

                ImportItem::create([
                    'import_id' => $import->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'remaining_quantity' => $quantity,  // Ban đầu đủ hàng
                    'unit_price' => $unitPrice,
                    'manufacture_date' => null,
                    'expired_at' => null,
                ]);

                $totalAmount += $itemTotal;
                $itemsInImport++;
            }

            // Update total_amount
            $import->update(['total_amount' => round($totalAmount, 2)]);

            $this->command->line("  📅 {$importDate->format('d/m')}: Import #{$import->id} - Nhà cung cấp: {$supplier->name} - {$itemsInImport} sản phẩm - " . number_format($totalAmount) . " VNĐ");

            $variantIndex = $endIdx;
        }

        $this->command->info("✅ Tạo imports thành công:");
        $this->command->info("   - 4 đợt nhập (ngày 2, 10, 18, 26)");
        $this->command->info("   - Tất cả 79 sản phẩm được nhập");
        $this->command->info("   - Mỗi sản phẩm: 15-35 units");
    }
}
