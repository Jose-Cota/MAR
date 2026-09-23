<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_map('current', DB::select('SHOW TABLES'));
foreach ($tables as $table) {
    if (strpos($table, 'riesgo') !== false || strpos($table, 'accion') !== false) {
        echo $table . "\n";
    }
}
