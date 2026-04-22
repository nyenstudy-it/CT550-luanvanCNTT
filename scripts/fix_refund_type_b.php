<?php
// Fix Type B: Roll back refund_status to 'pending' for orders with incomplete return workflow
// These orders have refund_status='completed' but return status is still 'requested'
// We need to pause the refund until admin properly approves and processes the return
require __DIR__ . '/../vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

$base = __DIR__ . '/..';
if (file_exists($base . '/bootstrap/app.php')) {
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

$opts = getopt('', ['apply']);
$apply = array_key_exists('apply', $opts);
echo $apply ? "APPLY MODE: Rolling back refund status\n" : "DRY-RUN: Simulating rollback\n";

// Type B orders: Return status is 'requested' but refund_status is 'completed'
$typeBOrders = [214, 240, 262, 263, 281, 294, 299];

echo "Orders to fix: " . count($typeBOrders) . "\n";
echo "Action: Set refund_status to 'pending' and clear refund_at until return is approved\n\n";

$csv = __DIR__ . '/../storage/reports/april_fix_type_b_log.csv';
$fp = fopen($csv, 'w');
fputcsv($fp, ['order_id','order_status','return_status','current_refund_status','action','notes']);

$updated = 0;

foreach ($typeBOrders as $orderId) {
    $order = DB::table('orders')->where('id', $orderId)->first();
    if (!$order) {
        echo "⚠ Order #$orderId not found\n";
        continue;
    }

    $returnRecord = DB::table('order_returns')->where('order_id', $orderId)->first();
    $payment = DB::table('payments')->where('order_id', $orderId)->whereNotNull('refund_status')->first();

    if (!$payment) {
        echo "⚠ Order #$orderId has no payment record with refund_status\n";
        continue;
    }

    $currentRefundStatus = $payment->refund_status ?? 'none';
    $returnStatus = $returnRecord?->status ?? 'NO_RECORD';

    $action = "Rollback refund_status to 'pending'";
    $notes = "Return workflow incomplete (status: {$returnStatus}). Admin must approve return and shipper must pickup before refund completes.";

    if ($apply) {
        // Roll back refund status to pending
        DB::table('payments')
            ->where('order_id', $orderId)
            ->update([
                'refund_status' => 'pending',
                'refund_at' => null, // Clear refund timestamp since it's not complete
                'updated_at' => Carbon::now()
            ]);

        echo "✓ Order #$orderId: Refund status rolled back to 'pending'\n";
        echo "  Return status: {$returnStatus}\n";
        echo "  Note: Admin must approve → Shipper pickup → Goods received → Inspection → Refund\n";
        fputcsv($fp, [$orderId, $order->status, $returnStatus, 'pending', $action, $notes]);
        $updated++;
    } else {
        echo "Order #$orderId: Would rollback refund_status from '{$currentRefundStatus}' to 'pending'\n";
        echo "  Return status: {$returnStatus}\n";
        fputcsv($fp, [$orderId, $order->status, $returnStatus, $currentRefundStatus, 'DRY-RUN: ' . $action, $notes]);
    }
}

fclose($fp);

echo "\n=== SUMMARY ===\n";
echo "Refund status rolled back: $updated / " . count($typeBOrders) . "\n";
echo "CSV log: $csv\n";

if ($apply) {
    echo "\n✓ Type B fix complete! All incomplete returns rolled back to pending.\n";
    echo "Note: Admin must now properly approve each return request and guide shipper pickup.\n";
} else {
    echo "\nTo apply: php scripts/fix_refund_type_b.php --apply\n";
}

exit;
