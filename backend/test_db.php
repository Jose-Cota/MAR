<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pys = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('ej.ejercicio', 2027)
    ->where('urg.numero', 15)
    ->where('py.numero', '35')
    ->select('py.proyecto_id', 'py.numero as py', 'urg.numero as urg', 'ej.ejercicio')
    ->get();

print_r($pys);

if ($pys->count() > 0) {
    $proyectoId = $pys->first()->proyecto_id;
    $metas = DB::connection('poa_prod')->table('metas')
        ->where('proyecto_id', $proyectoId)
        ->get();
    print_r($metas);
}
