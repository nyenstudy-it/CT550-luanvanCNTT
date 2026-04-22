<?php
$path = __DIR__ . '/../storage/logs/attendance-audit-2026-04-01_04-18.csv';
if (!file_exists($path)) {
    echo "File not found: $path\n";
    exit(1);
}

$f = fopen($path, 'r');
$h = fgetcsv($f);
$c = 0;
while (($r = fgetcsv($f)) !== false) {
    if (isset($r[14]) && trim($r[14]) !== '') $c++;
}
fclose($f);
echo $c . PHP_EOL;
