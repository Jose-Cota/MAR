<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Ejercicio 17 = 2027?
$ej = DB::table('ejercicios')->where('ejercicio_id', 17)->first();
echo "Ejercicio 17: " . json_encode($ej) . "\n\n";

// URGs for ejercicio 17
$urgs17 = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 17)->get();
echo "URGs para ejercicio 17: " . count($urgs17) . "\n";
foreach ($urgs17 as $u) {
    echo "  ID: {$u->unidad_responsable_gasto_id}, Num: {$u->numero}, Nombre: {$u->nombre}\n";
}

// URGs for ejercicio 19 (latest)
$urgs19 = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 19)->get();
echo "\nURGs para ejercicio 19: " . count($urgs19) . "\n";
foreach ($urgs19 as $u) {
    echo "  ID: {$u->unidad_responsable_gasto_id}, Num: {$u->numero}, Nombre: {$u->nombre}\n";
}
