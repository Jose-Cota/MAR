<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$urs = Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->get();
foreach($urs as $ur) {
    if ($ur->unidad_responsable_gasto_id > 500) {
        echo "ID: {$ur->unidad_responsable_gasto_id}, Nombre: {$ur->nombre}, Ejercicio: {$ur->ejercicio_id}\n";
    }
}
