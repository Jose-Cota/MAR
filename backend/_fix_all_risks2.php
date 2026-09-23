<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

$jsonToDbAction = [];
$actions = DB::table('acciones_sustantivas')
    ->join('proyectos', 'proyectos.proyecto_id', '=', 'acciones_sustantivas.proyecto_id')
    ->where('proyectos.ejercicio_id', 19)
    ->select('acciones_sustantivas.accion_sustantiva_id as db_id', 'acciones_sustantivas.descripcion')
    ->get();

foreach ($seed['poaActions'] ?? [] as $a) {
    if (($a['exercise'] ?? 0) == 2027) {
        $dbAct = collect($actions)->firstWhere('descripcion', $a['text']);
        if ($dbAct) {
            $jsonToDbAction[$a['id']] = $dbAct->db_id;
        }
    }
}

// Map prefixes to area_id
$prefixToAreaId = [];
$exactMap = [
    'PRES' => 1, 'SG' => 2, 'CGAJ' => 3, 'OIC' => 4, 'CGT' => 5, 'UCyG' => 6,
    'UPEyDH' => 7, 'CDP' => 8, 'DEF' => 9, 'IECC' => 10, 'SDGD' => 11,
    'SJ' => 12, 'SA' => 13, 'DRH' => 14, 'DRMSG' => 15, 'DPRF' => 16,
    'DSI' => 17, 'DGJ' => 18, 'CCSRP' => 19, 'CA' => 20,
    'MAG1' => 21, 'MAG2' => 22, 'MAG3' => 23, 'MAG4' => 24, 'MAG5' => 25
];

DB::beginTransaction();
try {
    DB::table('riesgo_controles')->whereIn('riesgo_id', function($q) { $q->select('id')->from('riesgos')->where('ejercicio_id', 19); })->delete();
    DB::table('riesgo_indicadores')->whereIn('riesgo_id', function($q) { $q->select('id')->from('riesgos')->where('ejercicio_id', 19); })->delete();
    DB::table('actividad_riesgo')->whereIn('riesgo_id', function($q) { $q->select('id')->from('riesgos')->where('ejercicio_id', 19); })->delete();
    DB::table('riesgos')->where('ejercicio_id', 19)->delete();

    $count = 0;
    foreach ($seed['risks'] ?? [] as $r) {
        if (($r['exercise'] ?? 0) == 2027 || strpos($r['id'], '2027') !== false) {
            $prefix = explode('-', $r['id'])[0];
            $areaId = $exactMap[$prefix] ?? 1;

            $rid = DB::table('riesgos')->insertGetId([
                'ejercicio_id' => 19,
                'area_id' => $areaId,
                'local_id' => $r['localId'],
                'riesgo' => $r['risk'] ?? '',
                'objetivo' => $r['objective'] ?? '',
                'factores' => $r['factors'] ?? '',
                
                'probabilidad' => $r['probability'] ?? 1,
                'impacto' => $r['impact'] ?? 1,
            ]);
            $count++;

            if (!empty($r['linkedActionIds'])) {
                foreach ($r['linkedActionIds'] as $linkedId) {
                    $dbAid = $jsonToDbAction[$linkedId] ?? null;
                    if ($dbAid) {
                        DB::table('actividad_riesgo')->insert([
                            'actividad_sustantiva_id' => $dbAid,
                            'riesgo_id' => $rid
                        ]);
                    }
                }
            }
        }
    }
    DB::commit();
    echo "SUCCESS: Inserted $count risks with precise area mapping!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
