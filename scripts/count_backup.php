<?php
$path = $argv[1] ?? __DIR__ . '/../storage/logs/attendance-audit-backup-2026-04-01_04-18.csv';
if (!file_exists($path)) {
    echo "0\n";
    exit(0);
}

$f = fopen($path, 'r');
$h = fgetcsv($f);
$c = 0;
while (($r = fgetcsv($f)) !== false) {
    $c++;
}
fclose($f);
echo $c . PHP_EOL;
