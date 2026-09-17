<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$alineaciones = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
    ->join('subprogramas', 'subprograma_pei_alineaciones.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
    ->join('pei_lineas_estrategicas', 'subprograma_pei_alineaciones.pei_linea_estrategica_id', '=', 'pei_lineas_estrategicas.pei_linea_estrategica_id')
    ->where('programas.ejercicio_id', 1) // Assuming 1 is 2026
    ->select('programas.numero as pg_num', 'programas.nombre as pg_nom', 
             'subprogramas.numero as sp_num', 'subprogramas.nombre as sp_nom',
             'pei_lineas_estrategicas.numero as pei_num', 'pei_lineas_estrategicas.nombre as pei_nom')
    ->orderBy('programas.numero')
    ->orderBy('subprogramas.numero')
    ->get();

$grouped = [];
foreach ($alineaciones as $al) {
    $pgKey = "PG {$al->pg_num}: {$al->pg_nom}";
    $spKey = "SP {$al->sp_num}: {$al->sp_nom}";
    
    if (!isset($grouped[$pgKey])) {
        $grouped[$pgKey] = [];
    }
    if (!isset($grouped[$pgKey][$spKey])) {
        $grouped[$pgKey][$spKey] = [];
    }
    
    $peiName = mb_substr($al->pei_nom, 0, 50) . "...";
    $grouped[$pgKey][$spKey][] = "Linea {$al->pei_num}: {$peiName}";
}

foreach ($grouped as $pg => $sps) {
    echo "$pg\n";
    foreach ($sps as $sp => $lineas) {
        echo "  - $sp\n";
        $uniqueLineas = array_unique($lineas);
        foreach ($uniqueLineas as $linea) {
            echo "      -> $linea\n";
        }
    }
}
