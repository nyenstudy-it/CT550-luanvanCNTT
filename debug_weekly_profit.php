<?php

/**
 * Debug Weekly Profit - Find why COGS > Revenue
 * Run in Laravel tinker or include this in a route
 */

require 'vendor/autoload.php';

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Simulating Laravel environment
// Run this in: php artisan tinker
// Or include it in a route temporarily

$start = Carbon::now()->startOfWeek();
$end = Carbon::now()->endOfWeek();

echo "=" . str_repeat("=", 80) . "\n";
echo "DEBUG: WEEKLY PROFIT CALCULATION (Week of {$start->format('Y-m-d')} to {$end->format('Y-m-d')})\n";
echo "=" . str_repeat("=", 80) . "\n\n";

// ============================================
// 1. Check REVENUE (using payments.paid_at)
// ============================================
echo "📊 REVENUE CALCULATION:\n";
echo str_repeat("-", 80) . "\n";

$revenueSql = DB::table('orders')
    ->join('payments', 'payments.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->where('payments.status', 'paid')
    ->whereBetween('payments.paid_at', [$start->startOfDay(), $end->endOfDay()])
    ->select(
        DB::raw('COUNT(DISTINCT orders.id) as order_count'),
        DB::raw('SUM(orders.total_amount) as total_amount'),
        DB::raw('SUM(orders.discount_amount) as total_discounts')
    );

$revenue = $revenueSql->first();
echo "Orders paid:     " . ($revenue->order_count ?? 0) . "\n";
echo "Total revenue:   " . number_format($revenue->total_amount ?? 0) . " ₫\n";
echo "Total discount:  " . number_format($revenue->total_discounts ?? 0) . " ₫\n\n";

// ============================================
// 2. Check REFUNDS (using payments.refund_at)
// ============================================
echo "📊 REFUND CALCULATION:\n";
echo str_repeat("-", 80) . "\n";

$refundSql = DB::table('payments')
    ->where('refund_status', 'completed')
    ->whereBetween('refund_at', [$start->startOfDay(), $end->endOfDay()])
    ->select(
        DB::raw('COUNT(*) as refund_count'),
        DB::raw('SUM(refund_amount) as total_refund')
    );

$refund = $refundSql->first();
echo "Refunds:         " . ($refund->refund_count ?? 0) . "\n";
echo "Total refund:    " . number_format($refund->total_refund ?? 0) . " ₫\n";

$netRevenue = ($revenue->total_amount ?? 0) - ($refund->total_refund ?? 0);
echo "NET REVENUE:     " . number_format($netRevenue) . " ₫\n\n";

// ============================================
// 3. Check COGS (using payments.paid_at)
// ============================================
echo "📊 COGS CALCULATION:\n";
echo str_repeat("-", 80) . "\n";

$cogsSql = DB::table('order_items')
    ->join('orders', 'orders.id', '=', 'order_items.order_id')
    ->join('payments', 'payments.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->where('payments.status', 'paid')
    ->whereBetween('payments.paid_at', [$start->startOfDay(), $end->endOfDay()])
    ->select(
        DB::raw('COUNT(order_items.id) as item_count'),
        DB::raw('SUM(order_items.quantity) as total_qty'),
        DB::raw('SUM(COALESCE(order_items.cost_price, 0) * order_items.quantity) as total_cogs'),
        DB::raw('AVG(COALESCE(order_items.cost_price, 0)) as avg_cost_price'),
        DB::raw('MIN(COALESCE(order_items.cost_price, 0)) as min_cost_price'),
        DB::raw('MAX(COALESCE(order_items.cost_price, 0)) as max_cost_price')
    );

$cogs = $cogsSql->first();
echo "Items sold:      " . ($cogs->item_count ?? 0) . "\n";
echo "Total quantity:  " . ($cogs->total_qty ?? 0) . "\n";
echo "Total COGS:      " . number_format($cogs->total_cogs ?? 0) . " ₫\n";
echo "Avg cost/item:   " . number_format($cogs->avg_cost_price ?? 0, 2) . " ₫\n";
echo "Min cost/item:   " . number_format($cogs->min_cost_price ?? 0, 2) . " ₫\n";
echo "Max cost/item:   " . number_format($cogs->max_cost_price ?? 0, 2) . " ₫\n\n";

// ============================================
// 4. COMPARE REVENUE vs COGS
// ============================================
echo "📊 REVENUE vs COGS COMPARISON:\n";
echo str_repeat("=", 80) . "\n";
printf("Revenue:         %20s ₫\n", number_format($netRevenue));
printf("COGS:            %20s ₫\n", number_format($cogs->total_cogs ?? 0));

$cogsPct = ($netRevenue > 0) ? (($cogs->total_cogs ?? 0) / $netRevenue * 100) : 0;
printf("COGS %% of Rev:   %20.2f%%\n\n", $cogsPct);

if (($cogs->total_cogs ?? 0) > $netRevenue) {
    echo "⚠️  ALERT: COGS > REVENUE!\n";
    echo "Possible causes:\n";
    echo "1. Cost prices set incorrectly (too high)\n";
    echo "2. Data from different date ranges\n";
    echo "3. Test/dummy data in database\n";
    echo "4. Quantity multiplier error\n\n";
} else {
    echo "✓ COGS ratio is normal\n\n";
}

// ============================================
// 5. Check for ANOMALIES
// ============================================
echo "📊 ANOMALY CHECK:\n";
echo str_repeat("-", 80) . "\n";

$anomalyOrders = DB::table('orders')
    ->join('payments', 'payments.order_id', '=', 'orders.id')
    ->join('order_items', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed')
    ->where('payments.status', 'paid')
    ->whereBetween('payments.paid_at', [$start->startOfDay(), $end->endOfDay()])
    ->select(
        'orders.id',
        'orders.total_amount',
        'orders.discount_amount',
        'order_items.cost_price',
        'order_items.quantity',
        DB::raw('order_items.cost_price * order_items.quantity as item_cogs'),
        'payments.paid_at'
    )
    ->orderBy(DB::raw('order_items.cost_price * order_items.quantity'), 'desc')
    ->limit(5)
    ->get();

echo "Top 5 orders by COGS:\n";
foreach ($anomalyOrders as $order) {
    printf(
        "Order #%d | Revenue: %s ₫ | COGS: %s ₫ | Ratio: %.1f%% | Discount: %s ₫\n",
        $order->id,
        number_format($order->total_amount),
        number_format($order->item_cogs),
        ($order->total_amount > 0 ? ($order->item_cogs / $order->total_amount * 100) : 0),
        number_format($order->discount_amount)
    );
}

echo "\n";
echo "=" . str_repeat("=", 80) . "\n";
echo "END DEBUG\n";
echo "=" . str_repeat("=", 80) . "\n";
