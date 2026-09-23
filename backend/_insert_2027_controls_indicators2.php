<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

$exactMap = [
    'PRES' => 1, 'SG' => 2, 'SA' => 3, 'DPyRF' => 4, 'DRH' => 5, 'DRMySG' => 6, 'CI' => 7, 'DGJ' => 8, 'CCSyRP' => 9, 'CTyDP' => 10, 'IFyC' => 11, 'CCLA' => 12, 'USI' => 13, 'UEyJ' => 14, 'CDyP' => 15, 'CA' => 16, 'CDHyG' => 17, 'DPPCyPD' => 18, 'CVyRI' => 19, 'UEPS' => 20, 'PAAH' => 21, 'PJHR' => 22, 'POVR' => 23, 'PKSL' => 24, 'PLPJC' => 25
];

$risksDb = DB::table('riesgos')->where('ejercicio_id', 19)->get();

DB::beginTransaction();
try {
    DB::table('riesgo_controles')->whereIn('riesgo_id', $risksDb->pluck('id'))->delete();
    DB::table('riesgo_indicadores')->whereIn('riesgo_id', $risksDb->pluck('id'))->delete();

    $controlsCount = 0;
    $indicatorsCount = 0;

    foreach ($seed['risks'] ?? [] as $r) {
        if (($r['exercise'] ?? 0) == 2027 || strpos($r['id'], '2027') !== false) {
            $prefix = explode('-', $r['id'])[0];
            $areaId = $exactMap[$prefix] ?? 1;
            $localId = $r['localId'];

            // Find matching risk in DB
            $dbRisk = collect($risksDb)->firstWhere(function($val) use ($areaId, $localId) {
                return $val->area_id == $areaId && $val->local_id == $localId;
            });

            if ($dbRisk) {
                foreach ($r['controls'] ?? [] as $c) {
                    DB::table('riesgo_controles')->insert([
                        'riesgo_id' => $dbRisk->id,
                        'texto' => $c['text'] ?? '',
                        'estado_validacion' => 'Propuesto',
                        'evidencia_tipo' => $c['evidence']['type'] ?? '',
                        'evidencia_referencia' => $c['evidence']['reference'] ?? '',
                        'evidencia_periodicidad' => $c['evidence']['periodicity'] ?? '',
                        'evidencia_responsable' => $c['evidence']['responsible'] ?? '',
                    ]);
                    $controlsCount++;
                }

                foreach ($r['indicators'] ?? [] as $i) {
                    DB::table('riesgo_indicadores')->insert([
                        'riesgo_id' => $dbRisk->id,
                        'nombre' => $i['name'] ?? '',
                        'tipo' => $i['type'] ?? '',
                        'periodicidad' => $i['periodicity'] ?? '',
                        'formula' => $i['formula'] ?? '',
                        'unidad_medida' => $i['unit'] ?? '',
                        'sentido' => $i['direction'] ?? '',
                    ]);
                    $indicatorsCount++;
                }
            }
        }
    }

    DB::commit();
    echo "SUCCESS: Inserted $controlsCount controls and $indicatorsCount indicators!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
