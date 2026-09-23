<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_map('current', DB::select('SHOW TABLES'));

$backup = [];
foreach ($tables as $table) {
    if (strpos($table, 'riesgo') !== false) {
        $backup[$table] = DB::table($table)->get();
    }
}

$filename = __DIR__ . '/backups/bdMR22Sept2026-21hrs.json';
file_put_contents($filename, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Backup de riesgos guardado en: " . $filename . "\n";
