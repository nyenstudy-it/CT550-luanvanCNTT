<?php
// Audit refund workflow: verify orders with refunds followed proper approval steps
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$base = __DIR__ . '/..';
if (file_exists($base . '/bootstrap/app.php')) {
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

echo "=== APRIL REFUND WORKFLOW AUDIT ===\n";
echo "Checking: Orders with refunds → verify proper approval workflow\n\n";

$from = '2026-04-01 00:00:00';
$to = '2026-04-18 23:59:59';

// Find orders with refund status
$ordersWithRefunds = DB::table('orders as o')
    ->leftJoin('payments as p', 'o.id', '=', 'p.order_id')
    ->whereBetween('o.created_at', [$from, $to])
    ->whereNotNull('p.refund_status')
    ->select('o.*', 'p.refund_status', 'p.refund_amount', 'p.refund_at')
    ->distinct()
    ->get();

echo "Orders with refund records found: " . $ordersWithRefunds->count() . "\n";

$csv = __DIR__ . '/../storage/reports/april_refund_workflow_audit.csv';
$fp = fopen($csv, 'w');
fputcsv($fp, ['order_id', 'status', 'refund_status', 'refund_amount', 'refund_at', 'return_request_id', 'return_status', 'workflow_approved', 'issues']);

$issues = [];

foreach ($ordersWithRefunds as $order) {
    // Check if there's a corresponding return request
    $returnRecord = null;
    if (Schema::hasTable('order_returns')) {
        $returnRecord = DB::table('order_returns')
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->first();
    }

    $returnId = $returnRecord?->id ?? null;
    $returnStatus = $returnRecord?->status ?? 'NO_RECORD';

    // Workflow check:
    // - If refund_status = 'completed', must have return record with proper workflow
    // - Must have passed through: requested -> approved -> given_to_shipper -> goods_received -> inspected -> then refund

    $workflowApproved = false;
    $issueList = [];

    if ($order->refund_status === 'completed') {
        if (!$returnRecord) {
            $issueList[] = "Refund completed but NO order_returns record found";
        } else {
            // Check workflow progression
            $validFinalStatuses = ['refunded', 'inspected_defective', 'inspected_good'];
            if (in_array($returnStatus, $validFinalStatuses)) {
                $workflowApproved = true;
            } else {
                $issueList[] = "Return status is '{$returnStatus}' but not in final approval state";
            }
        }
    } elseif ($order->refund_status === 'pending') {
        if ($returnRecord && in_array($returnStatus, ['inspected_defective', 'inspected_good'])) {
            $issueList[] = "Return inspection complete but refund still pending (should be completed)";
        }
    }

    $issueStr = implode('; ', $issueList);
    if (!empty($issueList)) {
        $issues[] = "Order #{$order->id}: " . $issueStr;
    }

    fputcsv($fp, [
        $order->id,
        $order->status,
        $order->refund_status ?? 'none',
        number_format($order->refund_amount ?? 0),
        $order->refund_at ?? '---',
        $returnId ?? '---',
        $returnStatus,
        $workflowApproved ? 'YES' : 'NO',
        $issueStr
    ]);
}

fclose($fp);

echo "\nResults:\n";
echo "CSV report: $csv\n";
echo "Orders with refunds: " . $ordersWithRefunds->count() . "\n";

if (empty($issues)) {
    echo "✓ All refunds follow proper workflow!\n";
} else {
    echo "\n⚠ ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
}

exit;
