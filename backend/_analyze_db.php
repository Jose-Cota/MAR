<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_map('current', DB::select('SHOW TABLES'));

foreach ($tables as $table) {
    if (strpos($table, 'riesgo') !== false || strpos($table, 'actividad') !== false || strpos($table, 'accione') !== false) {
        echo "Table: $table\n";
        $columns = DB::select("SHOW COLUMNS FROM `$table`");
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
        echo "\n";
    }
}
