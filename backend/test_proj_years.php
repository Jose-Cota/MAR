<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pys = [847, 897, 1402];
foreach($pys as $pid) {
    $py = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', $pid)->first();
    $ro = DB::connection('poa_prod')->table('responsables_operativos')->where('responsable_operativo_id', $py->responsable_operativo_id)->first();
    $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $ro->unidad_responsable_gasto_id)->first();
    $ej = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio_id', $urg->ejercicio_id)->first();
    echo "Proyecto: $pid -> Ejercicio: {$ej->ejercicio}\n";
}
