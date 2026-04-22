<?php
// Extract database schema for PDM/CDM diagram generation

$host = '127.0.0.1';
$db = 'senhong_ocop';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Get all tables
    $tables_result = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$db}' ORDER BY TABLE_NAME");
    
    $schema = [];
    
    while ($table = $tables_result->fetch_assoc()) {
        $table_name = $table['TABLE_NAME'];
        
        // Get columns
        $columns_result = $conn->query("
            SELECT 
                COLUMN_NAME,
                COLUMN_TYPE,
                IS_NULLABLE,
                COLUMN_KEY,
                EXTRA,
                COLUMN_DEFAULT
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
        
        // Get foreign keys
        $fk_result = $conn->query("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$table_name}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $foreign_keys = [];
        while ($fk = $fk_result->fetch_assoc()) {
            $foreign_keys[] = [
                'column' => $fk['COLUMN_NAME'],
                'references_table' => $fk['REFERENCED_TABLE_NAME'],
                'references_column' => $fk['REFERENCED_COLUMN_NAME'],
                'constraint' => $fk['CONSTRAINT_NAME']
            ];
        }
        
        $schema[$table_name] = [
            'columns' => $columns,
            'primary_keys' => $primary_keys,
            'foreign_keys' => $foreign_keys
        ];
    }
    
    // Output as formatted JSON
    echo "<pre>";
    echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
    // Save to file
    file_put_contents('schema.json', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<br><strong>Schema saved to schema.json</strong>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
