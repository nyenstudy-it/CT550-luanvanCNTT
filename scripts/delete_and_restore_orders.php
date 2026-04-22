<?php
// Usage: php scripts/delete_and_restore_orders.php [--apply]
// This will: for each order_id in the list, restore import_items.remaining_quantity
// according to order_items.batch_details, then delete order_items, payments, order_returns, and orders.
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
echo $apply ? "APPLY MODE: making DB changes\n" : "DRY-RUN: no DB changes (use --apply to execute)\n";

// list of order ids to remove (selected by analysis)
$orderIds = [244,272,282,217,219,202,238,298,269,205,295,224,279,296,292,246,255,200,206,235,290,233,274,239,209,181,223,252,300,226,258,280,194,291,286,197,283];

$summary = ['processed'=>0,'restored_qty'=>0,'errors'=>[]];

foreach ($orderIds as $oid) {
    echo "Processing order: $oid\n";
    try {
        DB::transaction(function() use($oid,&$apply,&$summary) {
            // load items
            $items = DB::table('order_items')->where('order_id',$oid)->get();
            foreach ($items as $it) {
                $bd = $it->batch_details;
                if (empty($bd)) continue;
                $alloc = json_decode($bd, true);
                if (!is_array($alloc)) continue;
                foreach ($alloc as $a) {
                    $importId = $a['import_item_id'] ?? ($a['id'] ?? null);
                    $qty = intval($a['qty'] ?? ($a['quantity'] ?? 0));
                    if (!$importId || $qty <= 0) continue;
                    $imp = DB::table('import_items')->where('id',$importId)->first();
                    if (!$imp) continue;
                    $newRem = ($imp->remaining_quantity ?? 0) + $qty;
                    // cap to original quantity if available
                    if (property_exists($imp,'quantity')) {
                        $newRem = min($newRem, $imp->quantity);
                    }
                    if ($apply) {
                        DB::table('import_items')->where('id',$importId)->update(['remaining_quantity'=>$newRem,'updated_at'=>Carbon::now()]);
                    }
                    $summary['restored_qty'] += $qty;
                }
            }

            if ($apply) {
                // delete dependent records
                DB::table('order_items')->where('order_id',$oid)->delete();
                DB::table('payments')->where('order_id',$oid)->delete();
                if (Schema::hasTable('order_returns')) {
                    DB::table('order_returns')->where('order_id',$oid)->delete();
                }
                DB::table('orders')->where('id',$oid)->delete();
            }
            $summary['processed']++;
        }, 5);
    } catch (Exception $e) {
        $summary['errors'][] = "order $oid: " . $e->getMessage();
        echo "Error processing $oid: " . $e->getMessage() . "\n";
    }
}

echo "Done. Orders processed: " . $summary['processed'] . "\n";
echo "Total restored quantity (sum of allocations): " . $summary['restored_qty'] . "\n";
if (!empty($summary['errors'])) {
    echo "Errors:\n" . implode("\n", $summary['errors']) . "\n";
}

exit;
