<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ros = DB::table('responsables_operativos')
    ->where('ejercicio_id', 17)
    ->where('unidad_responsable_gasto_id', 2)
    ->get();

echo "Responsables Operativos for UR 2 and Ejercicio 17: " . count($ros) . "\n";
foreach($ros as $ro) {
    echo "RO ID: " . $ro->responsable_operativo_id . "\n";
}

$all_ros = DB::table('responsables_operativos')
    ->where('ejercicio_id', 17)
    ->get();
echo "Total ROs for Ejercicio 17: " . count($all_ros) . "\n";
