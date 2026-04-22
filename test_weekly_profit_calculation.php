<?php

/**
 * Test Weekly Profit Calculation Logic
 * Verified: April 22, 2026
 * 
 * Kiểm tra công thức tính lợi nhuận tuần đã sửa
 */

// Ví dụ dữ liệu tuần
$testWeek = [
    'period' => 'Tuần 16 (15/04 - 21/04)',

    // Doanh thu
    'revenue' => 5_000_000,  // Tổng tiền khách trả (đã bao gồm giảm giá)
    'discounts' => 500_000,   // Mức giảm (tracking riêng)

    // Chi phí bán hàng
    'cogs' => 2_500_000,      // Giá vốn hàng bán
    'shipping_cost' => 300_000, // Phí vận chuyển

    // Chi phí hoạt động
    'staff_cost' => 800_000,  // Lương nhân viên
    'inventory_shrinkage' => 50_000,  // Hao hụt hàng hết hạn
    'writeoff_cost' => 20_000, // Hàng hủy
];

// ============================================
// CÔNG THỨC CỦA (CŨ - SAI)
// ============================================
$oldGrossProfit = $testWeek['revenue']
    - $testWeek['discounts']           // ❌ LỖIB: Tính kép giảm giá
    - $testWeek['cogs']
    - $testWeek['shipping_cost'];

$oldNetProfit = $oldGrossProfit
    - $testWeek['staff_cost']
    - $testWeek['inventory_shrinkage']
    - $testWeek['writeoff_cost'];

// ============================================
// CÔNG THỨC MỚI (ĐÚNG)
// ============================================
$newGrossProfit = $testWeek['revenue'] - $testWeek['cogs'];  // Chỉ trừ vốn
$newOperatingProfit = $newGrossProfit - $testWeek['shipping_cost'];  // Trừ vận chuyển
$newNetProfit = $newOperatingProfit
    - $testWeek['staff_cost']
    - $testWeek['inventory_shrinkage']
    - $testWeek['writeoff_cost'];

// ============================================
// HIỂN THỊ KẾT QUẢ
// ============================================
echo "=" . str_repeat("=", 78) . "\n";
echo "KIỂM TRA TÍNH LỢI NHUẬN TUẦN\n";
echo "=" . str_repeat("=", 78) . "\n\n";

echo "📊 DỮ LIỆU TUẦN: {$testWeek['period']}\n";
echo str_repeat("-", 80) . "\n";
printf("Doanh thu (Revenue):         %15s ₫\n", number_format($testWeek['revenue']));
printf("Giảm giá (tracked):          %15s ₫\n", number_format($testWeek['discounts']));
printf("Giá vốn (COGS):              %15s ₫\n", number_format($testWeek['cogs']));
printf("Phí vận chuyển:              %15s ₫\n", number_format($testWeek['shipping_cost']));
printf("Lương nhân viên:             %15s ₫\n", number_format($testWeek['staff_cost']));
printf("Hao hụt hàng:                %15s ₫\n", number_format($testWeek['inventory_shrinkage']));
printf("Hàng hủy:                    %15s ₫\n", number_format($testWeek['writeoff_cost']));

echo "\n";
echo "❌ CÔNG THỨC CŨ (SAI - Tính kép giảm giá):\n";
echo str_repeat("-", 80) . "\n";
echo "Gross Profit = Revenue - Discounts - COGS - Shipping\n";
printf(
    "            = %d - %d - %d - %d\n",
    $testWeek['revenue'],
    $testWeek['discounts'],
    $testWeek['cogs'],
    $testWeek['shipping_cost']
);
printf("            = %s ₫  ❌ SAI\n\n", number_format($oldGrossProfit));

printf(
    "Net Profit = %d - %d - %d - %d\n",
    $oldGrossProfit,
    $testWeek['staff_cost'],
    $testWeek['inventory_shrinkage'],
    $testWeek['writeoff_cost']
);
printf("           = %s ₫  ❌ SAI\n\n", number_format($oldNetProfit));

echo "✓ CÔNG THỨC MỚI (ĐÚNG):\n";
echo str_repeat("-", 80) . "\n";
echo "Gross Profit = Revenue - COGS (Chỉ trừ vốn)\n";
printf("            = %d - %d\n", $testWeek['revenue'], $testWeek['cogs']);
printf("            = %s ₫\n\n", number_format($newGrossProfit));

echo "Operating Profit = Gross Profit - Shipping\n";
printf("                = %d - %d\n", $newGrossProfit, $testWeek['shipping_cost']);
printf("                = %s ₫\n\n", number_format($newOperatingProfit));

echo "Net Profit = Operating Profit - Staff - Shrinkage - Writeoff\n";
printf(
    "           = %d - %d - %d - %d\n",
    $newOperatingProfit,
    $testWeek['staff_cost'],
    $testWeek['inventory_shrinkage'],
    $testWeek['writeoff_cost']
);
printf("           = %s ₫  ✓ ĐÚNG\n\n", number_format($newNetProfit));

// ============================================
// SO SÁNH
// ============================================
$difference = $newNetProfit - $oldNetProfit;
$percentDiff = ($difference / abs($oldNetProfit)) * 100;

echo "📈 SO SÁNH KẾT QUẢ:\n";
echo str_repeat("=", 80) . "\n";
printf("Lợi nhuận ròng (CŨ):         %20s ₫\n", number_format($oldNetProfit));
printf("Lợi nhuận ròng (MỚI):        %20s ₫\n", number_format($newNetProfit));
printf("Chênh lệch:                  %20s ₫\n", number_format($difference));
printf("Phần trăm thay đổi:          %20.2f%%\n", $percentDiff);

echo "\n";
if ($newNetProfit > $oldNetProfit) {
    echo "✓ KHOÁ PHÁT HIỆN: Lợi nhuận được tính CHÍNH XÁC hơn (+" . number_format($difference) . " ₫)\n";
    echo "  Nguyên nhân: Không còn tính kép giảm giá\n";
} else {
    echo "⚠ CẢNH BÁO: Lợi nhuận giảm\n";
}

echo "\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "KÊNKHÁC CẶN KIỂM TRA:\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "1. ✓ Giảm giá (Discounts):\n";
echo "   - Tracking riêng để theo dõi\n";
echo "   - KHÔNG trừ lại từ Revenue (vì đã bao gồm)\n\n";

echo "2. ✓ Profit Breakdown:\n";
printf("   - Gross Profit:     %s ₫ (Revenue - COGS)\n", number_format($newGrossProfit));
printf("   - Operating Profit: %s ₫ (Gross - Shipping)\n", number_format($newOperatingProfit));
printf("   - Net Profit:       %s ₫ (Operating - Others)\n\n", number_format($newNetProfit));

echo "3. ✓ Profit Margin:\n";
$profitMargin = ($newNetProfit / $testWeek['revenue']) * 100;
printf("   - %.2f%% (Net Profit / Revenue)\n\n", $profitMargin);

echo "4. ⚠ Cần Kiểm Tra Thêm:\n";
echo "   - Inventory Shrinkage logic (từ import_items.created_at)\n";
echo "   - Salary allocation ratio\n";
echo "   - Date fields consistency (orders.updated_at vs paid_at)\n";

echo "\n=" . str_repeat("=", 80) . "\n";
echo "✓ KIỂM TRA HOÀN TẤT\n";
echo "=" . str_repeat("=", 80) . "\n";
