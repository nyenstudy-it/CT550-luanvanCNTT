<?php
// Usage: php scripts/fix_payments_match_orders.php [--apply]
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
echo $apply ? "APPLY MODE: will modify payments\n" : "DRY-RUN: no DB changes\n";

$from = '2026-04-01 00:00:00';
$to = '2026-04-18 23:59:59';

$orders = DB::table('orders')->whereBetween('created_at', [$from, $to])->get();
echo "Orders in range: " . $orders->count() . "\n";

$csv = __DIR__ . '/../storage/reports/april_payments_fix.csv';
$fp = fopen($csv, 'w');
fputcsv($fp, ['order_id', 'stored_total', 'payments_sum', 'action', 'details']);

$paymentsCols = Schema::hasTable('payments') ? Schema::getColumnListing('payments') : [];

$actions = 0;

foreach ($orders as $order) {
    $orderTotal = (float) ($order->total_amount ?? 0);
    $payments = DB::table('payments')->where('order_id', $order->id)->orderByDesc('id')->get();
    $paymentsSum = (float) DB::table('payments')->where('order_id', $order->id)->sum('amount');

    if (abs($orderTotal - $paymentsSum) < 0.001) {
        // matched
        continue;
    }

    $diff = $orderTotal - $paymentsSum; // positive => need to increase payments
    $detail = '';

    if ($diff > 0) {
        if ($payments->count() > 0) {
            // increase latest payment
            $p = $payments->first();
            $newAmt = (float)$p->amount + $diff;
            $detail = "increase payment id {$p->id} by {$diff} to {$newAmt}";
            if ($apply) {
                DB::table('payments')->where('id', $p->id)->update(['amount' => $newAmt, 'updated_at' => Carbon::now()]);
            }
        } else {
            // insert a new payment record
            $detail = "insert payment amount {$orderTotal}";
            if ($apply) {
                $data = [];
                if (in_array('order_id', $paymentsCols)) $data['order_id'] = $order->id;
                if (in_array('amount', $paymentsCols)) $data['amount'] = $orderTotal;
                if (in_array('method', $paymentsCols)) $data['method'] = 'COD';
                if (in_array('status', $paymentsCols)) $data['status'] = 'paid';
                if (in_array('paid_at', $paymentsCols)) $data['paid_at'] = Carbon::now();
                if (in_array('created_at', $paymentsCols)) $data['created_at'] = Carbon::now();
                if (in_array('updated_at', $paymentsCols)) $data['updated_at'] = Carbon::now();
                DB::table('payments')->insert($data);
            }
        }
    } else {
        // diff < 0 => paymentsSum > orderTotal: need to reduce payments by -diff
        $toReduce = -$diff;
        $detailParts = [];
        foreach ($payments as $p) {
            if ($toReduce <= 0) break;
            $amt = (float)$p->amount;
            $reduce = min($amt, $toReduce);
            $newAmt = $amt - $reduce;
            $detailParts[] = "set payment {$p->id} from {$amt} to {$newAmt}";
            if ($apply) {
                DB::table('payments')->where('id', $p->id)->update(['amount' => $newAmt, 'updated_at' => Carbon::now()]);
            }
            $toReduce -= $reduce;
        }
        if ($toReduce > 0) {
            // still owe reduction; insert a negative refund payment to balance
            $detailParts[] = "insert negative refund of {$toReduce}";
            if ($apply) {
                $data = [];
                if (in_array('order_id', $paymentsCols)) $data['order_id'] = $order->id;
                if (in_array('amount', $paymentsCols)) $data['amount'] = -$toReduce;
                if (in_array('method', $paymentsCols)) $data['method'] = 'COD';
                if (in_array('status', $paymentsCols)) $data['status'] = 'refunded';
                if (in_array('paid_at', $paymentsCols)) $data['paid_at'] = Carbon::now();
                if (in_array('created_at', $paymentsCols)) $data['created_at'] = Carbon::now();
                if (in_array('updated_at', $paymentsCols)) $data['updated_at'] = Carbon::now();
                DB::table('payments')->insert($data);
            }
        }
        $detail = implode('; ', $detailParts);
    }

    fputcsv($fp, [$order->id, $orderTotal, $paymentsSum, $apply ? 'APPLY' : 'DRY-RUN', $detail]);
    $actions++;
}

fclose($fp);
echo "Processed orders with payment mismatches: $actions. CSV: $csv\n";
exit;
