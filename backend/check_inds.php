<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urg = '04';
$ro = '10';
$pg = '03';
$sp = '07';
$py = '08';
$ejercicio = 2026;

$proyecto = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
    ->where('ejercicios.ejercicio', $ejercicio)
    ->where('unidades_responsables_gastos.numero', $urg)
    ->where('responsables_operativos.numero', $ro)
    ->where('programas.numero', $pg)
    ->where('subprogramas.numero', $sp)
    ->where('proyectos.numero', $py)
    ->select('proyectos.*')
    ->first();

if (!$proyecto) {
    echo "Proyecto no encontrado en $ejercicio.\n";
    return;
}

echo "Old Proyecto ID: {$proyecto->proyecto_id}\n";
$inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $proyecto->proyecto_id)->get();
echo "Indicadores: " . count($inds) . "\n";
