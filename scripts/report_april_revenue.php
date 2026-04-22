<?php
// scripts/report_april_revenue.php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT DATE(o.created_at) AS day, COALESCE(SUM(p.amount),0) AS revenue, COUNT(o.id) AS orders FROM orders o LEFT JOIN payments p ON p.order_id=o.id AND p.status='paid' WHERE o.created_at BETWEEN '2026-04-01' AND '2026-04-18 23:59:59' GROUP BY day ORDER BY day");

echo "day\trevenue\torders\n";
foreach ($rows as $r) {
    echo $r->day . "\t" . number_format($r->revenue, 0, '.', ',') . "\t" . $r->orders . "\n";
}

// Check for missing days and print zero rows
$existing = array_map(fn($r) => $r->day, $rows);
$start = new DateTime('2026-04-01');
$end = new DateTime('2026-04-18');
$interval = new DateInterval('P1D');
$period = new DatePeriod($start, $interval, $end->modify('+1 day'));
foreach ($period as $dt) {
    $d = $dt->format('Y-m-d');
    if (!in_array($d, $existing)) {
        echo $d . "\t0\t0\n";
    }
}
