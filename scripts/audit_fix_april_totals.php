<?php
// Usage: php scripts/audit_fix_april_totals.php [--apply]
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

$opts = getopt('', ['apply']);
$apply = array_key_exists('apply', $opts);
echo $apply ? "APPLY MODE: will update DB\n" : "DRY-RUN: no DB changes\n";

$from = '2026-04-01 00:00:00';
$to = Carbon::now()->endOfDay()->toDateTimeString();

$orders = DB::table('orders')->whereBetween('created_at', [$from, $to])->get();
echo "Orders found in period: " . $orders->count() . "\n";

$csvPath = __DIR__ . '/../storage/reports/april_totals_audit.csv';
$fp = fopen($csvPath, 'w');
fputcsv($fp, ['order_id','created_at','stored_subtotal','computed_subtotal','stored_total_amount','computed_total_amount','shipping','discount','payment_sum','mismatch']);

$mismatchCount = 0;

foreach ($orders as $order) {
    $items = DB::table('order_items')->where('order_id', $order->id)->get();
    $storedSubtotal = $items->sum('subtotal');
    $computedSubtotal = 0;
    foreach ($items as $it) {
        $computedSubtotal += ((float)$it->price) * ((int)$it->quantity);
    }

    $shipping = $order->shipping_fee ?? 0;
    $discount = $order->discount_amount ?? 0;

    $storedTotal = $order->total_amount ?? ($storedSubtotal + $shipping - $discount);
    $computedTotal = $computedSubtotal + $shipping - $discount;

    $paymentSum = DB::table('payments')->where('order_id', $order->id)->sum('amount');

    $mismatch = ($storedSubtotal != $computedSubtotal) || (abs($storedTotal - $computedTotal) > 0.001);
    if ($mismatch) $mismatchCount++;

    fputcsv($fp, [$order->id, $order->created_at, $storedSubtotal, $computedSubtotal, $storedTotal, $computedTotal, $shipping, $discount, $paymentSum, $mismatch ? 'YES' : 'NO']);

    // In apply mode, do NOT auto-change payments. Only fix item.subtotal and order.total_amount to computed values.
    if ($apply && $mismatch) {
        foreach ($items as $it) {
            $newSubtotal = ((float)$it->price) * ((int)$it->quantity);
            DB::table('order_items')->where('id', $it->id)->update(['subtotal' => $newSubtotal]);
        }
        $newOrderTotal = $computedSubtotal + $shipping - $discount;
        DB::table('orders')->where('id', $order->id)->update(['total_amount' => $newOrderTotal]);
    }
}

fclose($fp);
echo "Mismatch orders: $mismatchCount. CSV written to: $csvPath\n";
if ($apply) echo "Applied fixes: updated item.subtotal and orders.total_amount for mismatches. Note: payments not adjusted.\n";

exit;
