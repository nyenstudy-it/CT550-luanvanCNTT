<?php
$host = '127.0.0.1';
$db = 'senhong_ocop';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    file_put_contents('schema_error.txt', 'Connection failed: ' . $conn->connect_error);
    die();
}

$tables_result = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$db}' ORDER BY TABLE_NAME");

$schema = [];

while ($table = $tables_result->fetch_assoc()) {
    $table_name = $table['TABLE_NAME'];
    
    $columns_result = $conn->query("
        SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$table_name}'
        ORDER BY ORDINAL_POSITION
    ");
    
    $columns = [];
    $primary_keys = [];
    
    while ($col = $columns_result->fetch_assoc()) {
        $columns[$col['COLUMN_NAME']] = [
            'type' => $col['COLUMN_TYPE'],
            'nullable' => $col['IS_NULLABLE'],
            'key' => $col['COLUMN_KEY'],
            'extra' => $col['EXTRA'],
            'default' => $col['COLUMN_DEFAULT']
        ];
        
        if ($col['COLUMN_KEY'] === 'PRI') {
            $primary_keys[] = $col['COLUMN_NAME'];
        }
    }
    
    $fk_result = $conn->query("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$table_name}' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $foreign_keys = [];
    while ($fk = $fk_result->fetch_assoc()) {
        $foreign_keys[] = [
            'column' => $fk['COLUMN_NAME'],
            'references_table' => $fk['REFERENCED_TABLE_NAME'],
            'references_column' => $fk['REFERENCED_COLUMN_NAME']
        ];
    }
    
    $schema[$table_name] = [
        'columns' => $columns,
        'primary_keys' => $primary_keys,
        'foreign_keys' => $foreign_keys
    ];
}

file_put_contents('schema.json', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Success";
$conn->close();
?>
