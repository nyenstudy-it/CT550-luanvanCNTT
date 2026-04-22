<?php
$dsn = 'mysql:host=127.0.0.1;dbname=senhong_ocop;charset=utf8mb4;port=3306';
$user = 'root';
$pass = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo "ERROR: Could not connect to DB: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$queries = [
    'returns_distribution' => "SELECT reason, COUNT(*) AS cnt FROM order_returns WHERE created_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59' GROUP BY reason ORDER BY cnt DESC;",

    'refund_by_reason' => "SELECT r.reason, COUNT(*) AS total FROM order_returns r JOIN orders o ON o.id = r.order_id JOIN payments p ON p.order_id = o.id WHERE p.refund_status = 'completed' AND p.refund_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59' GROUP BY r.reason ORDER BY total DESC;",

    'payments_refunded_summary' => "SELECT COUNT(*) AS refunded_orders, IFNULL(SUM(refund_amount),0) AS total_refunded FROM payments WHERE refund_status = 'completed' AND refund_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59';",

    'payments_paid_summary' => "SELECT COUNT(*) AS paid_count, IFNULL(SUM(amount),0) AS paid_total FROM payments WHERE status = 'paid' AND paid_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59';",

    'orders_summary' => "SELECT COUNT(*) AS orders_count, IFNULL(SUM(total_amount),0) AS orders_total FROM orders WHERE created_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59';",
    // Detailed diagnostics to find why paid_count > orders_count
    'distinct_paid_orders_in_april' => "SELECT COUNT(DISTINCT order_id) AS paid_orders_in_april FROM payments WHERE status = 'paid' AND paid_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59';",

    'payments_count_per_order_for_april_orders' => "SELECT p.order_id, COUNT(*) AS payments_cnt, GROUP_CONCAT(p.id ORDER BY p.id SEPARATOR ',') AS payment_ids, SUM(p.amount) AS total_paid FROM payments p JOIN orders o ON o.id = p.order_id WHERE o.created_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59' GROUP BY p.order_id HAVING payments_cnt > 1 ORDER BY payments_cnt DESC;",

    'payments_paid_in_april_order_not_in_april' => "SELECT p.id, p.order_id, p.method, p.status, p.amount, p.paid_at, o.created_at AS order_created_at FROM payments p JOIN orders o ON o.id = p.order_id WHERE p.paid_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59' AND NOT (o.created_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59') ORDER BY p.paid_at DESC LIMIT 200;",

    'payments_for_april_orders' => "SELECT p.id, p.order_id, p.method, p.status, p.amount, p.paid_at FROM payments p JOIN orders o ON o.id = p.order_id WHERE o.created_at BETWEEN '2026-04-01' AND '2026-04-30 23:59:59' ORDER BY p.order_id, p.paid_at;",
];

foreach ($queries as $key => $sql) {
    echo "--- $key ---\n";
    try {
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            echo "(no rows)\n\n";
            continue;
        }
        // print header
        $cols = array_keys($rows[0]);
        echo implode("\t|\t", $cols) . "\n";
        foreach ($rows as $r) {
            echo implode("\t|\t", array_map(function ($v) {
                return (string)$v;
            }, $r)) . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "ERROR running query ($key): " . $e->getMessage() . "\n\n";
    }
}

echo "Done.\n";
