<?php
// Comprehensive verification: check if all April orders have fully aligned data
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

$base = __DIR__ . '/..';
if (file_exists($base . '/bootstrap/app.php')) {
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

$from = '2026-04-01 00:00:00';
$to = '2026-04-18 23:59:59';

$orders = DB::table('orders')->whereBetween('created_at', [$from, $to])->get();
echo "=== APRIL ORDERS SYNC VERIFICATION ===\n";
echo "Period: 2026-04-01 to 2026-04-18\n";
echo "Total orders: " . $orders->count() . "\n\n";

$csv = __DIR__ . '/../storage/reports/april_complete_verification.csv';
$fp = fopen($csv, 'w');
fputcsv($fp, ['order_id', 'items_count', 'subtotal_stored', 'subtotal_computed', 'subtotal_match', 'shipping', 'discount', 'total_stored', 'total_expected', 'total_match', 'payments_sum', 'payments_match', 'all_sync']);

$syncCount = 0;
$errors = [];

foreach ($orders as $order) {
    $items = DB::table('order_items')->where('order_id', $order->id)->get();
    $itemsCount = $items->count();

    // Compute item totals
    $subtotalStored = (float)$items->sum('subtotal');
    $subtotalComputed = 0;
    $itemMismatches = 0;

    foreach ($items as $it) {
        $computed = ((float)$it->price) * ((int)$it->quantity);
        $subtotalComputed += $computed;
        if (abs((float)$it->subtotal - $computed) > 0.001) {
            $itemMismatches++;
        }
    }

    $subtotalMatch = (abs($subtotalStored - $subtotalComputed) < 0.001);

    // Order total
    $shipping = (float)($order->shipping_fee ?? 0);
    $discount = (float)($order->discount_amount ?? 0);
    $totalStored = (float)($order->total_amount ?? 0);
    $totalExpected = $subtotalComputed + $shipping - $discount;
    $totalMatch = (abs($totalStored - $totalExpected) < 0.001);

    // Payments
    $paymentsSum = (float)DB::table('payments')->where('order_id', $order->id)->sum('amount');
    $paymentsMatch = (abs($paymentsSum - $totalStored) < 0.001);

    // All in sync?
    $allSync = ($subtotalMatch && $totalMatch && $paymentsMatch && $itemMismatches == 0);
    if ($allSync) $syncCount++;

    fputcsv($fp, [
        $order->id,
        $itemsCount,
        $subtotalStored,
        $subtotalComputed,
        $subtotalMatch ? 'YES' : 'NO',
        $shipping,
        $discount,
        $totalStored,
        $totalExpected,
        $totalMatch ? 'YES' : 'NO',
        $paymentsSum,
        $paymentsMatch ? 'YES' : 'NO',
        $allSync ? 'YES' : 'NO'
    ]);

    if (!$allSync) {
        $err = "Order #{$order->id}: ";
        if (!$subtotalMatch) $err .= "subtotal mismatch ({$subtotalStored} vs {$subtotalComputed}); ";
        if (!$totalMatch) $err .= "total mismatch ({$totalStored} vs {$totalExpected}); ";
        if (!$paymentsMatch) $err .= "payments mismatch ({$paymentsSum} vs {$totalStored}); ";
        if ($itemMismatches > 0) $err .= "{$itemMismatches} item(s) subtotal mismatch";
        $errors[] = $err;
    }
}

fclose($fp);

echo "Fully synced orders: $syncCount / " . $orders->count() . "\n";
echo "CSV report: $csv\n\n";

if (!empty($errors)) {
    echo "MISMATCHES FOUND:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
} else {
    echo "✓ ALL ORDERS ARE FULLY SYNCED!\n";
}

exit;
