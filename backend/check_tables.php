<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $v = (array)$t;
    $n = array_values($v)[0];
    if (strpos($n, 'activ') !== false || strpos($n, 'accio') !== false || strpos($n, 'accion') !== false) {
        echo $n . "\n";
    }
}
echo "\n--- Tables with 'poa' ---\n";
foreach ($tables as $t) {
    $v = (array)$t;
    $n = array_values($v)[0];
    if (strpos($n, 'poa') !== false) {
        echo $n . "\n";
    }
}
