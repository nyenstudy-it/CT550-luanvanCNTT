<?php
// php scripts/april_profit_summary.php
require __DIR__ . '/../vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

$base = __DIR__ . '/..';
if (file_exists($base . '/bootstrap/app.php')) {
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

$start='2026-04-01'; $end='2026-04-18';
$paid = DB::table('payments')
    ->join('orders','payments.order_id','=','orders.id')
    ->whereBetween(DB::raw('DATE(orders.created_at)'), [$start, $end])
    ->sum('payments.amount');

$ship = DB::table('orders')
    ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
    ->sum('shipping_fee');

$cost = DB::table('order_items')
    ->join('orders','order_items.order_id','=','orders.id')
    ->whereBetween(DB::raw('DATE(orders.created_at)'), [$start, $end])
    ->selectRaw('SUM(order_items.quantity * IFNULL(order_items.cost_price,0)) as c')
    ->value('c');

echo "Period: $start to $end\n";
echo "Total paid: " . number_format($paid,0,',','.') . "\n";
echo "Total shipping: " . number_format($ship,0,',','.') . "\n";
echo "Total cost (COGS): " . number_format($cost,0,',','.') . "\n";
$profit = $paid - $ship - $cost;
echo "Estimated profit: " . number_format($profit,0,',','.') . "\n";
exit;
