<?php
// Load Laravel environment
require __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// MySQL connection
$host = env('DB_HOST');
$user = env('DB_USERNAME');
$password = env('DB_PASSWORD');
$database = env('DB_DATABASE');

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Get all tables
$tables_result = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$database'");
$tables = [];
while ($row = $tables_result->fetch_assoc()) {
    $tables[] = $row['TABLE_NAME'];
}

$schema = [];

// For each table, get columns, keys, and foreign keys
foreach ($tables as $table) {
    $table_info = [
        'name' => $table,
        'columns' => [],
        'primary_key' => [],
        'foreign_keys' => [],
        'row_count' => 0
    ];

    // Get column info
    $columns_result = $conn->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'");

    while ($col = $columns_result->fetch_assoc()) {
        $column_info = [
            'name' => $col['COLUMN_NAME'],
            'type' => $col['DATA_TYPE'],
            'nullable' => $col['IS_NULLABLE'] === 'YES',
            'key' => $col['COLUMN_KEY'],
            'extra' => $col['EXTRA'],
            'comment' => $col['COLUMN_COMMENT']
        ];
        $table_info['columns'][] = $column_info;

        if ($col['COLUMN_KEY'] === 'PRI') {
            $table_info['primary_key'][] = $col['COLUMN_NAME'];
        }
    }

    // Get foreign keys
    $fk_result = $conn->query("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

    while ($fk = $fk_result->fetch_assoc()) {
        $table_info['foreign_keys'][] = [
            'constraint' => $fk['CONSTRAINT_NAME'],
            'column' => $fk['COLUMN_NAME'],
            'referenced_table' => $fk['REFERENCED_TABLE_NAME'],
            'referenced_column' => $fk['REFERENCED_COLUMN_NAME']
        ];
    }

    // Get row count
    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
    $count_row = $count_result->fetch_assoc();
    $table_info['row_count'] = $count_row['cnt'];

    $schema[] = $table_info;
}

$conn->close();

// Output as JSON
header('Content-Type: application/json');
echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
