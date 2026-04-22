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

// Count affected rows before
$before = $pdo->query("SELECT COUNT(*) AS cnt FROM order_returns WHERE reason = 'refund_request_retroactive'")->fetch();
echo "Rows with refund_request_retroactive before: " . ($before['cnt'] ?? 0) . PHP_EOL;

if ((int)($before['cnt'] ?? 0) === 0) {
    echo "No rows to update.\n";
    exit(0);
}

// Update to canonical code for 'Giao sai sản phẩm'
$stmt = $pdo->prepare("UPDATE order_returns SET reason = :new WHERE reason = :old");
$stmt->execute([':new' => 'wrong_product', ':old' => 'refund_request_retroactive']);
$affected = $stmt->rowCount();

echo "Updated rows: " . $affected . PHP_EOL;

$after = $pdo->query("SELECT COUNT(*) AS cnt FROM order_returns WHERE reason = 'refund_request_retroactive'")->fetch();
echo "Rows with refund_request_retroactive after: " . ($after['cnt'] ?? 0) . PHP_EOL;

$nowCanonical = $pdo->query("SELECT COUNT(*) AS cnt FROM order_returns WHERE reason = 'wrong_product'")->fetch();
echo "Rows with reason = 'wrong_product' now: " . ($nowCanonical['cnt'] ?? 0) . PHP_EOL;

echo "Done.\n";
