<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find the project in 2026
$project2026 = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->where('ejercicios.ejercicio', 2026)
    ->where('unidades_responsables_gastos.numero', '04')
    ->where('proyectos.numero', '08')
    ->select('proyectos.proyecto_id', 'proyectos.nombre', 'proyectos.numero as py_num', 'subprogramas.numero as sp_num')
    ->first();

if (!$project2026) {
    echo "Project not found in 2026\n";
    exit;
}

echo "Found 2026 Project: {$project2026->sp_num}.{$project2026->py_num} - {$project2026->nombre} (ID: {$project2026->proyecto_id})\n";

// Get PEI alignments for this project in 2026
$alineaciones = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
    ->where('proyecto_id', $project2026->proyecto_id)
    ->get();

echo "PEI Alignments in 2026:\n";
foreach ($alineaciones as $al) {
    echo "- Linea Estrategica ID: {$al->pei_linea_estrategica_id}, Objetivo ID: {$al->pei_objetivo_estrategico_id}\n";
}

// Check 2027 project
$project2027 = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->where('ejercicios.ejercicio', 2027)
    ->where('unidades_responsables_gastos.numero', '04')
    ->where('proyectos.numero', '08')
    ->select('proyectos.proyecto_id', 'proyectos.nombre')
    ->first();

if ($project2027) {
    echo "\nFound 2027 Project: {$project2027->nombre} (ID: {$project2027->proyecto_id})\n";
    $al2027 = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
        ->where('proyecto_id', $project2027->proyecto_id)
        ->get();
    echo "PEI Alignments in 2027:\n";
    foreach ($al2027 as $al) {
        echo "- Linea Estrategica ID: {$al->pei_linea_estrategica_id}, Objetivo ID: {$al->pei_objetivo_estrategico_id}\n";
    }
}
