<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_map('current', DB::select('SHOW TABLES'));

foreach ($tables as $table) {
    $columns = DB::select("SHOW COLUMNS FROM `$table`");
    foreach ($columns as $column) {
        if (strpos($column->Field, 'riesgo') !== false || strpos($column->Field, 'accion') !== false) {
            echo "Table: $table -> Column: {$column->Field}\n";
        }
    }
}
