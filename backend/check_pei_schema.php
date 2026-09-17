<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['pei_programas', 'pei_lineas_estrategicas', 'pei_objetivos_estrategicos'];
foreach ($tables as $t) {
    echo "Table: $t\n";
    $columns = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing($t);
    print_r($columns);
    echo "\n";
}

$sampleLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->take(3)->get();
echo "Sample Lineas:\n";
print_r($sampleLineas);

$sampleObjetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->take(3)->get();
echo "Sample Objetivos:\n";
print_r($sampleObjetivos);

