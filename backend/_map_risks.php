<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$actionDescMap2026 = [];
foreach ($data['poaActions'] ?? [] as $act) {
    $actionDescMap2026[$act['id']] = $act['text'];
}

$dbActions = DB::table('acciones_sustantivas')
    ->join('proyectos', 'acciones_sustantivas.proyecto_id', '=', 'proyectos.proyecto_id')
    ->where('proyectos.ejercicio_id', 17)
    ->select('acciones_sustantivas.accion_sustantiva_id', 'acciones_sustantivas.descripcion', 'proyectos.nombre as proyecto')
    ->get();

function cleanStr($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace(
        ['á','é','í','ó','ú','ñ','ü','.',',',';','(',')',' '], 
        ['a','e','i','o','u','n','u','','','','','',''], 
        $str
    );
    return trim($str);
}

$dbActionsMap = [];
foreach ($dbActions as $dbAct) {
    $dbActionsMap[] = [
        'id' => $dbAct->accion_sustantiva_id,
        'desc' => cleanStr($dbAct->descripcion),
        'orig' => $dbAct->descripcion,
        'proj' => cleanStr($dbAct->proyecto)
    ];
}

$inserts = [];
foreach ($data['risks'] ?? [] as $r) {
    // 2026 risks only
    if (strpos($r['id'], '2026') !== false && !empty($r['linkedActionIds'])) {
        // Find risk in DB (ejercicio_id = 17 for 2026)
        $dbRisk = DB::table('riesgos')->where('local_id', $r['localId'])->where('ejercicio_id', 17)->first();
        if (!$dbRisk) continue; // skip if risk not imported
        
        foreach ($r['linkedActionIds'] as $aid) {
            $jsonText = $actionDescMap2026[$aid] ?? '';
            $cleanJson = cleanStr($jsonText);
            
            $bestMatch = null;
            $bestDistance = 999;
            foreach ($dbActionsMap as $dbA) {
                // If one contains the other
                if (strpos($cleanJson, $dbA['desc']) !== false || strpos($dbA['desc'], $cleanJson) !== false) {
                    $bestMatch = $dbA['id'];
                    break;
                }
                
                // Levenshtein
                $lev = levenshtein($cleanJson, $dbA['desc']);
                if ($lev < $bestDistance && $lev < 15) { // max 15 edit distance
                    $bestMatch = $dbA['id'];
                    $bestDistance = $lev;
                }
            }
            if ($bestMatch) {
                $inserts[] = [
                    'actividad_sustantiva_id' => $bestMatch,
                    'riesgo_id' => $dbRisk->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                echo "Matched: [$jsonText] -> DB_ID: {$bestMatch}\n";
            } else {
                echo "NO MATCH for: [$jsonText]\n";
            }
        }
    }
}
echo "Found " . count($inserts) . " mappings to insert.\n";
if (count($inserts) > 0) {
    // Delete existing links for these risks
    $riskIdsToUpdate = array_unique(array_column($inserts, 'riesgo_id'));
    DB::table('actividad_riesgo')->whereIn('riesgo_id', $riskIdsToUpdate)->delete();
    
    // Insert new mappings
    DB::table('actividad_riesgo')->insert($inserts);
    echo "Successfully inserted " . count($inserts) . " links.\n";
}
