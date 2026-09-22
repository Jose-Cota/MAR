<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// JSON extraído sin modificaciones de Sistema_MAR_TECDMX_2026_2027_v5_1.html.
$sourcePath = 'C:/Cota/MAR/scratch_seed.json';
$sourceJson = file_get_contents($sourcePath);
if ($sourceJson === false) {
    throw new RuntimeException('No se encontró la extracción del archivo fuente.');
}

$seed = json_decode($sourceJson, true, flags: JSON_THROW_ON_ERROR);
$areaIds = [];
foreach ($seed['areas'] as $index => $area) {
    // Las áreas de la base fueron creadas en el mismo orden que la fuente.
    $areaIds[$area['id']] = $index + 1;
}

$exerciseIds = DB::table('ejercicios')
    ->whereIn('ejercicio', [2026, 2027])
    ->pluck('ejercicio_id', 'ejercicio')
    ->all();

$apply = in_array('--apply', $_SERVER['argv'] ?? [], true) || getenv('MAR_SYNC_APPLY') === '1';
$force = getenv('MAR_SYNC_FORCE') === '1';
$summary = [
    'source' => 0,
    'matched' => 0,
    'missing' => [],
    'changed' => 0,
    'unchanged' => 0,
];

DB::transaction(function () use ($seed, $areaIds, $exerciseIds, $apply, $force, &$summary) {
    foreach ($seed['risks'] as $sourceRisk) {
        $summary['source']++;
        $areaId = $areaIds[$sourceRisk['areaId']] ?? null;
        $exerciseId = $exerciseIds[$sourceRisk['exercise']] ?? null;

        if (!$areaId || !$exerciseId) {
            $summary['missing'][] = $sourceRisk['id'] . ' (área o ejercicio no configurado)';
            continue;
        }

        $risk = DB::table('riesgos')
            ->where('area_id', $areaId)
            ->where('ejercicio_id', $exerciseId)
            ->where('local_id', $sourceRisk['localId'])
            ->first();

        if (!$risk) {
            $summary['missing'][] = $sourceRisk['id'];
            continue;
        }

        $summary['matched']++;
        $sourceControls = $sourceRisk['controls'] ?? [];
        $sourceIndicators = $sourceRisk['indicators'] ?? [];
        $currentControls = DB::table('riesgo_controles')->where('riesgo_id', $risk->id)->get()->map(fn ($row) => (array) $row)->all();
        $currentIndicators = DB::table('riesgo_indicadores')->where('riesgo_id', $risk->id)->get()->map(fn ($row) => (array) $row)->all();

        $controlChanged = count($currentControls) !== count($sourceControls);
        if (!$controlChanged) {
            foreach ($sourceControls as $index => $control) {
                if (($currentControls[$index]['texto'] ?? null) !== ($control['text'] ?? '')) {
                    $controlChanged = true;
                    break;
                }
            }
        }

        $indicatorChanged = count($currentIndicators) !== count($sourceIndicators);
        if (!$indicatorChanged) {
            foreach ($sourceIndicators as $index => $indicator) {
                $current = $currentIndicators[$index];
                $expected = [
                    'nombre' => $indicator['name'] ?? '',
                    'formula' => $indicator['formula'] ?? '',
                    'numerador' => $indicator['numeratorLabel'] ?? '',
                    'denominador' => $indicator['denominatorLabel'] ?? '',
                    'periodicidad' => $indicator['periodicity'] ?? '',
                ];
                foreach ($expected as $column => $value) {
                    if (($current[$column] ?? null) !== $value) {
                        $indicatorChanged = true;
                        break 2;
                    }
                }
            }
        }

        if (!$force && !$controlChanged && !$indicatorChanged) {
            $summary['unchanged']++;
            continue;
        }

        $summary['changed']++;
        if (!$apply) {
            continue;
        }

        DB::table('riesgo_controles')->where('riesgo_id', $risk->id)->delete();
        foreach ($sourceControls as $control) {
            DB::table('riesgo_controles')->insert([
                'riesgo_id' => $risk->id,
                'texto' => $control['text'] ?? '',
                'estado_validacion' => $control['validationStatus'] ?? 'Propuesto – pendiente de validación',
                'evidencia_tipo' => $control['evidence']['type'] ?? '',
                'evidencia_referencia' => $control['evidence']['reference'] ?? '',
                'evidencia_periodicidad' => $control['evidence']['periodicity'] ?? '',
                'evidencia_responsable' => $control['evidence']['responsible'] ?? '',
                'evidencia_link' => $control['evidence']['link'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('riesgo_indicadores')->where('riesgo_id', $risk->id)->delete();
        foreach ($sourceIndicators as $indicator) {
            DB::table('riesgo_indicadores')->insert([
                'riesgo_id' => $risk->id,
                'nombre' => $indicator['name'] ?? '',
                'tipo' => $indicator['type'] ?? null,
                'periodicidad' => $indicator['periodicity'] ?? null,
                'formula' => $indicator['formula'] ?? null,
                'unidad' => $indicator['unit'] ?? null,
                'sentido' => $indicator['direction'] ?? null,
                'numerador' => $indicator['numeratorLabel'] ?? null,
                'denominador' => $indicator['denominatorLabel'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
});

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
