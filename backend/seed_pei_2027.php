<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicio2027_id = 18; // 2027

// Map SP -> [Linea_Num => [Obj_Nums]]
$mapping = [
    '01' => [2 => [1,2,3,4,5], 3 => [2,3]],
    '02' => [2 => [1,2,3,4,5], 3 => [2,3]],
    '03' => [2 => [1,2,3,4,5]],
    '04' => [1 => [1,3]],
    '05' => [1 => [1,3]],
    '06' => [1 => [2]],
    '07' => [5 => [3]],
    '08' => [5 => [3]],
    '09' => [5 => [2]],
    '10' => [5 => [4]],
    '11' => [5 => [4]],
    '12' => [3 => [1,5]],
    '13' => [3 => [4]],
    '14' => [5 => [1,5]],
    '15' => [5 => [1,5]],
    '16' => [5 => [1,5]],
    '17' => [4 => [1,2,3]],
    '18' => [4 => [1,2,3]],
];

DB::connection('poa_prod')->beginTransaction();
try {
    // Get PEI program for 2027
    $peiPrograma = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2027)->first();
    if (!$peiPrograma) {
        throw new Exception("No PEI program for 2027");
    }
    
    // Cache Lineas and Objetivos
    $lineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $peiPrograma->pei_programa_id)->get()->keyBy('numero');
    $objetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
        ->whereIn('pei_linea_estrategica_id', $lineas->pluck('pei_linea_estrategica_id'))
        ->get();
        
    $objMap = []; // [linea_id][obj_numero] = obj_id
    foreach ($objetivos as $o) {
        $objMap[$o->pei_linea_estrategica_id][$o->numero] = $o->pei_objetivo_estrategico_id;
    }
    
    // Get all subprogramas for 2027
    $subprogramas = DB::connection('poa_prod')->table('subprogramas')
        ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
        ->where('programas.ejercicio_id', $ejercicio2027_id)
        ->select('subprogramas.*')
        ->get();
        
    // Insert mapping
    $inserted = 0;
    foreach ($subprogramas as $sp) {
        $num = $sp->numero;
        if (isset($mapping[$num])) {
            foreach ($mapping[$num] as $lineaNum => $objNums) {
                if (!isset($lineas[$lineaNum])) continue;
                $lineaId = $lineas[$lineaNum]->pei_linea_estrategica_id;
                
                foreach ($objNums as $objNum) {
                    $objId = $objMap[$lineaId][$objNum] ?? null;
                    if ($objId) {
                        DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->insert([
                            'subprograma_id' => $sp->subprograma_id,
                            'pei_linea_estrategica_id' => $lineaId,
                            'pei_objetivo_estrategico_id' => $objId,
                        ]);
                        $inserted++;
                    }
                }
            }
        }
    }
    
    DB::connection('poa_prod')->commit();
    echo "Inserted $inserted PEI alignments for 2027 successfully!\n";
    
} catch (Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
