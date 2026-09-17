<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fetch 5 random projects from 2025 that have indicators
$proyectos2025 = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->where('ejercicios.ejercicio', 2025)
    ->whereExists(function($q) {
        $q->select(DB::raw(1))->from('indicadores')->whereColumn('indicadores.proyecto_id', 'proyectos.proyecto_id');
    })
    ->select('proyectos.proyecto_id', 'proyectos.nombre')
    ->take(3)
    ->get();

foreach ($proyectos2025 as $py) {
    echo "Proyecto 2025 (ID: {$py->proyecto_id}): " . substr($py->nombre, 0, 40) . "...\n";
    $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $py->proyecto_id)->get();
    foreach ($inds as $ind) {
        $metaTipo = DB::connection('poa_prod')->table('metas')->where('meta_id', $ind->meta_id)->value('tipo');
        $metaPyId = DB::connection('poa_prod')->table('metas')->where('meta_id', $ind->meta_id)->value('proyecto_id');
        echo "  - Ind: {$ind->indicador_id} | meta_id: {$ind->meta_id} ({$metaTipo}) | meta_proyecto_id: {$metaPyId}\n";
    }
}
