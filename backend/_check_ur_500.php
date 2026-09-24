<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$urs = Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', '>', 500)->get();
echo 'Found: ' . $urs->count() . "\n";
foreach($urs as $u) {
    echo "ID: {$u->unidad_responsable_gasto_id}, Nombre: {$u->nombre}, Ejercicio: {$u->ejercicio_id}\n";
}
