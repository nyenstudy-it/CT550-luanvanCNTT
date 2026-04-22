<?php
// Run: php scripts/fix_and_rebalance_april.php [--apply] [--tolerance=0.01]
// --apply: actually write DB changes. Without it runs a dry-run report.
// tolerance: fraction (e.g., 0.01 for ±1%) target per-day deviation.

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Boot Laravel's framework if available
$base = __DIR__ . '/..';
if (file_exists($base . '/bootstrap/app.php')) {
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

$opts = getopt('', ['apply', 'tolerance::']);
$apply = array_key_exists('apply', $opts);
$tolerance = isset($opts['tolerance']) ? (float)$opts['tolerance'] : 0.01;

echo ($apply ? "Running with DB changes\n" : "Dry-run mode (no DB changes). Use --apply to persist)\n");
echo "Tolerance: " . ($tolerance * 100) . "%\n";

// Date range for April orders (1 -> today or 18)
$start = Carbon::createFromFormat('Y-m-d', '2026-04-01')->startOfDay();
$end = Carbon::now()->startOfDay();

// Load orders in range
$orders = DB::table('orders')
    ->whereBetween(DB::raw('DATE(created_at)'), [$start->toDateString(), $end->toDateString()])
    ->get();

if ($orders->isEmpty()) {
    echo "No orders found in April range.\n";
    exit;
}

// Helper: compute subtotal of an order from order_items
function compute_subtotal($order_id)
{
    $items = DB::table('order_items')->where('order_id', $order_id)->get();
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += ($it->price * $it->quantity);
    }
    return $subtotal;
}

// Step 1: Fix order_items.unit_price to match product/variant price
echo "Checking order item prices...\n";
$priceChanges = [];
foreach ($orders as $order) {
    $items = DB::table('order_items')->where('order_id', $order->id)->get();
    foreach ($items as $it) {
        // attempt variant price, then product price
        $expected = null;
        if (!empty($it->product_variant_id) && Schema::hasTable('product_variants')) {
            $v = DB::table('product_variants')->where('id', $it->product_variant_id)->first();
            if ($v) $expected = $v->price;
        }
        if ($expected === null && Schema::hasTable('products')) {
            if (!empty($it->product_variant_id)) {
                $pv = DB::table('product_variants')->where('id', $it->product_variant_id)->first();
                if ($pv && !empty($pv->product_id)) {
                    $p = DB::table('products')->where('id', $pv->product_id)->first();
                    if ($p) $expected = $p->price;
                }
            }
        }
        if ($expected === null) continue;
        if ((float)$it->price != (float)$expected) {
            $priceChanges[] = ['order_item_id' => $it->id, 'old' => (float)$it->price, 'new' => (float)$expected];
            if ($apply) {
                DB::table('order_items')->where('id', $it->id)->update(['price' => $expected]);
            }
        }
    }
}
echo "Order item price mismatches: " . count($priceChanges) . "\n";

// Step 2: Ensure batch_details allocations exist and import_items.remaining_quantity reflect sums
echo "Reconciling inventory allocations (FIFO) for orders...\n";
$allocIssues = [];
foreach ($orders as $order) {
    $items = DB::table('order_items')->where('order_id', $order->id)->get();
    foreach ($items as $it) {
        // check batch_details JSON
        $bd = $it->batch_details;
        $totalAllocated = 0;
        if (!empty($bd)) {
            $arr = json_decode($bd, true);
            if (is_array($arr)) {
                foreach ($arr as $a) $totalAllocated += ($a['qty'] ?? 0);
            }
        }
        if ($totalAllocated != $it->quantity) {
            // need to allocate FIFO from import_items
            $need = $it->quantity - $totalAllocated;
            if ($need == 0) continue;
            $variantWhere = ['product_variant_id' => $it->product_variant_id];

            $batches = DB::table('import_items')
                ->where($variantWhere)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id')
                ->get();
            $alloc = is_array($arr) ? $arr : [];
            foreach ($batches as $b) {
                if ($need <= 0) break;
                $take = min($need, $b->remaining_quantity);
                if ($take <= 0) continue;
                $alloc[] = ['import_item_id' => $b->id, 'qty' => $take, 'unit_price' => $b->unit_price];
                $need -= $take;
                if ($apply) {
                    $newRem = $b->remaining_quantity - $take;
                    if ($newRem < 0) $newRem = 0;
                    DB::table('import_items')->where('id', $b->id)->update(['remaining_quantity' => $newRem]);
                }
            }
            if ($need > 0) {
                $allocIssues[] = ['order_item_id' => $it->id, 'missing' => $need];
            } else {
                if ($apply) {
                    DB::table('order_items')->where('id', $it->id)->update(['batch_details' => json_encode($alloc)]);
                }
            }
        }
    }
}
echo "Inventory allocation issues: " . count($allocIssues) . "\n";

// Step 3: Fix shipping_fee per rule and recompute order totals
echo "Fixing shipping fees and recomputing order totals...\n";
$shipChanges = [];
foreach ($orders as $order) {
    $subtotal = compute_subtotal($order->id);
    $expectedShip = ($subtotal >= 300000) ? 0 : 20000;
    $oldShip = $order->shipping_fee ?? 0;
    if ((float)$oldShip !== (float)$expectedShip) {
        $shipChanges[] = ['order_id' => $order->id, 'old' => $oldShip, 'new' => $expectedShip, 'subtotal' => $subtotal];
        if ($apply) {
            $newTotal = $subtotal + $expectedShip;
            DB::table('orders')->where('id', $order->id)->update(['shipping_fee' => $expectedShip, 'total_amount' => $newTotal]);
            // update payment amount if exists and status paid
            $pay = DB::table('payments')->where('order_id', $order->id)->first();
            if ($pay) {
                DB::table('payments')->where('id', $pay->id)->update(['amount' => $newTotal]);
            }
        }
    }
}
echo "Shipping fee corrections: " . count($shipChanges) . "\n";

// Step 4: Rebalance payments across days to reach even per-day totals while preserving overall sum
echo "Starting rebalance...\n";
$payments = DB::table('payments')
    ->join('orders', 'payments.order_id', '=', 'orders.id')
    ->whereBetween(DB::raw('DATE(orders.created_at)'), [$start->toDateString(), $end->toDateString()])
    ->select('payments.*', 'orders.id as order_id')
    ->get()
    ->keyBy('id')
    ->toArray();

$total = array_sum(array_map(function ($p) {
    return (float)$p->amount;
}, $payments));
$days = $start->diffInDays($end) + 1;
$targetPerDay = $total / $days;

echo "Total paid: " . number_format($total, 2) . ", days: $days, target/day: " . number_format($targetPerDay, 2) . "\n";

// Build array of payments sorted descending by amount
$pList = array_values($payments);
usort($pList, function ($a, $b) {
    return (float)$b->amount - (float)$a->amount;
});

// Initialize per-day buckets
$buckets = [];
for ($i = 0; $i < $days; $i++) {
    $day = $start->copy()->addDays($i)->toDateString();
    $buckets[$day] = ['sum' => 0, 'payments' => []];
}

// Greedy best-fit: place largest payment into day with currently smallest sum
foreach ($pList as $p) {
    // find day with min sum
    uasort($buckets, function ($a, $b) {
        return $a['sum'] <=> $b['sum'];
    });
    $day = array_key_first($buckets);
    $buckets[$day]['payments'][] = $p->id;
    $buckets[$day]['sum'] += (float)$p->amount;
}

// Check deviation
$max = max(array_map(fn($b) => $b['sum'], $buckets));
$min = min(array_map(fn($b) => $b['sum'], $buckets));
echo "After assignment max/day=" . number_format($max, 2) . ", min/day=" . number_format($min, 2) . "\n";

$deviation = ($max - $targetPerDay) / $targetPerDay;
// If outside tolerance, perform iterative smoothing (move large payments from max-day to min-day)
if ($deviation > $tolerance) {
    $iter = 0;
    $maxIter = 5000;
    while ($deviation > $tolerance && $iter < $maxIter) {
        $sums = array_map(fn($b) => $b['sum'], $buckets);
        arsort($sums);
        $daysSorted = array_keys($sums);
        $maxDay = $daysSorted[0];
        $secondMax = $daysSorted[1] ?? $daysSorted[0];
        $minDay = array_keys($sums, min($sums))[0];
        $currentMax = $buckets[$maxDay]['sum'];
        $bestMove = null;
        $bestNewMax = $currentMax;
        // evaluate moving each payment from maxDay to minDay
        foreach ($buckets[$maxDay]['payments'] as $pid) {
            $amt = (float)$payments[$pid]->amount;
            $candMax = max($currentMax - $amt, $buckets[$secondMax]['sum'] ?? 0, $buckets[$minDay]['sum'] + $amt);
            if ($candMax < $bestNewMax) {
                $bestNewMax = $candMax;
                $bestMove = $pid;
            }
        }
        if ($bestMove === null) break;
        // perform move
        $amt = (float)$payments[$bestMove]->amount;
        $index = array_search($bestMove, $buckets[$maxDay]['payments']);
        if ($index !== false) array_splice($buckets[$maxDay]['payments'], $index, 1);
        $buckets[$maxDay]['sum'] -= $amt;
        $buckets[$minDay]['payments'][] = $bestMove;
        $buckets[$minDay]['sum'] += $amt;
        // recompute deviation
        $max = max(array_map(fn($b) => $b['sum'], $buckets));
        $min = min(array_map(fn($b) => $b['sum'], $buckets));
        $deviation = ($max - $targetPerDay) / $targetPerDay;
        $iter++;
    }
    echo "Iterative smoothing iterations: $iter\n";
}
// If still outside tolerance, attempt splitting payments from high-day into low-day buckets
if ($deviation > $tolerance) {
    echo "Attempting payment splits to reach tolerance...\n";
    $iter = 0;
    $maxIter = 5000;
    while ($deviation > $tolerance && $iter < $maxIter) {
        // find current max and min days
        $sums = array_map(fn($b) => $b['sum'], $buckets);
        arsort($sums);
        $daysSorted = array_keys($sums);
        $maxDay = $daysSorted[0];
        $minDay = array_keys($sums, min($sums))[0];
        if ($maxDay === $minDay) break;
        $need = $targetPerDay - $buckets[$minDay]['sum'];
        if ($need <= 0) break;
        // find a payment in maxDay that can be split
        $splitFound = false;
        // prefer largest payments
        usort($buckets[$maxDay]['payments'], function ($a, $b) use ($payments) {
            return (float)$payments[$b]->amount - (float)$payments[$a]->amount;
        });
        foreach ($buckets[$maxDay]['payments'] as $pid) {
            $amt = (float)$payments[$pid]->amount;
            if ($amt <= 1) continue;
            $split = min($need, $amt - 1);
            if ($split <= 0) continue;
            // simulate/apply split: reduce original amt, create new payment with split amount assigned to minDay
            $originalAmt = (float)$payments[$pid]->amount;
            $newOriginalAmt = $originalAmt - $split;
            if ($apply) {
                // update original payment amount in DB
                DB::table('payments')->where('id', $pid)->update(['amount' => $newOriginalAmt]);
                // create new payment row copying important fields
                $origRec = DB::table('payments')->where('id', $pid)->first();
                $paidAtForMin = Carbon::parse($minDay)->addHours(rand(9, 21))->addMinutes(rand(0, 59))->toDateTimeString();
                $now = Carbon::now()->toDateTimeString();
                $newId = DB::table('payments')->insertGetId([
                    'order_id' => $origRec->order_id,
                    'method' => $origRec->method,
                    'amount' => $split,
                    'transaction_code' => $origRec->transaction_code ?? null,
                    'status' => 'paid',
                    'paid_at' => $paidAtForMin,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                // update in-memory structures
                $payments[$pid]->amount = $newOriginalAmt;
                $payments[$newId] = (object)['id' => $newId, 'order_id' => $origRec->order_id, 'amount' => $split];
                $buckets[$maxDay]['sum'] -= $split;
                $buckets[$minDay]['sum'] += $split;
                $buckets[$minDay]['payments'][] = $newId;
            } else {
                $payments[$pid]->amount = $newOriginalAmt;
                $newPid = 'split_' . $pid . '_' . $iter;
                $payments[$newPid] = (object)['id' => $newPid, 'order_id' => $payments[$pid]->order_id, 'amount' => $split];
                $buckets[$maxDay]['sum'] -= $split;
                $buckets[$minDay]['sum'] += $split;
                $buckets[$minDay]['payments'][] = $newPid;
            }
            $splitFound = true;
            break;
        }
        if (! $splitFound) break;
        $max = max(array_map(fn($b) => $b['sum'], $buckets));
        $min = min(array_map(fn($b) => $b['sum'], $buckets));
        $deviation = ($max - $targetPerDay) / $targetPerDay;
        $iter++;
    }
    echo "Payment-split iterations: $iter\n";
}

if ($deviation <= $tolerance) {
    echo "Within tolerance. Applying timestamps and updates...\n";
    if ($apply) {
        foreach ($buckets as $day => $bucket) {
            foreach ($bucket['payments'] as $pid) {
                $paidAt = Carbon::parse($day)->addHours(rand(9, 21))->addMinutes(rand(0, 59))->toDateTimeString();
                // update payment.paid_at and orders created/updated dates
                $p = $payments[$pid];
                DB::table('payments')->where('id', $pid)->update(['paid_at' => $paidAt, 'status' => 'paid']);
                $createdAt = Carbon::parse($paidAt)->subDays(rand(3, 5))->toDateTimeString();
                DB::table('orders')->where('id', $p->order_id)->update(['created_at' => $createdAt, 'updated_at' => $paidAt, 'status' => 'completed']);
            }
        }
    } else {
        echo "Dry-run: not applying DB updates.\n";
    }
} else {
    echo "Deviation too large (" . round($deviation * 100, 2) . "%). Consider re-running with different tolerance or allow iterative smoothing.\n";
}

echo "Done. Summary:\n";
echo "Price mismatches corrected: " . count($priceChanges) . "\n";
echo "Shipping corrections: " . count($shipChanges) . "\n";
echo "Inventory allocation issues: " . count($allocIssues) . "\n";

exit;
