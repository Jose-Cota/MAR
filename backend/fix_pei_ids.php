<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::connection('poa_prod')->beginTransaction();
    $prog2027 = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2027)->first();
    $prog2026 = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2026)->first();
    
    if (!$prog2027 || !$prog2026) {
        echo "Missing PEI programs\n";
        return;
    }

    $oldLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $prog2026->pei_programa_id)->get();
    $newLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $prog2027->pei_programa_id)->get();
    $mapLineas = [];
    foreach ($oldLineas as $oL) {
        $nL = $newLineas->firstWhere('numero', $oL->numero);
        if ($nL) $mapLineas[$oL->pei_linea_estrategica_id] = $nL->pei_linea_estrategica_id;
    }

    $oldObjs = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_linea_estrategica_id', $oldLineas->pluck('pei_linea_estrategica_id'))->get();
    $newObjs = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_linea_estrategica_id', $newLineas->pluck('pei_linea_estrategica_id'))->get();
    $mapObjs = [];
    foreach ($oldObjs as $oO) {
        $nO = $newObjs->where('numero', $oO->numero)->first(); // Not perfect if 'numero' is repeated across lineas, but sufficient usually
        // Better: match by old linea mapping
        $nO = $newObjs->where('pei_linea_estrategica_id', $mapLineas[$oO->pei_linea_estrategica_id])->where('numero', $oO->numero)->first();
        if ($nO) $mapObjs[$oO->pei_objetivo_estrategico_id] = $nO->pei_objetivo_estrategico_id;
    }

    $aligns = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
        ->where('pei_programa_id', $prog2027->pei_programa_id)
        ->get();
        
    $fixed = 0;
    foreach ($aligns as $al) {
        $upd = [];
        if (isset($mapLineas[$al->pei_linea_estrategica_id])) {
            $upd['pei_linea_estrategica_id'] = $mapLineas[$al->pei_linea_estrategica_id];
        }
        if (isset($mapObjs[$al->pei_objetivo_estrategico_id])) {
            $upd['pei_objetivo_estrategico_id'] = $mapObjs[$al->pei_objetivo_estrategico_id];
        }
        if (!empty($upd)) {
            DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
                ->where('pei_proyecto_alineacion_id', $al->pei_proyecto_alineacion_id)
                ->update($upd);
            $fixed++;
        }
    }
    DB::connection('poa_prod')->commit();
    echo "Fixed $fixed project alignments in 2027.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
