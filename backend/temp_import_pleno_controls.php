<?php
$plenoAreas = DB::table('unidades_responsables_gastos')
    ->where('nombre', 'like', '%pleno%')
    ->pluck('unidad_responsable_gasto_id')
    ->toArray();

$countCtrl = 0;
$riesgos = DB::table('riesgos')->get();
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);

foreach ($riesgos as $r) {
    if (in_array($r->area_id, $plenoAreas) && preg_match('/^R\d+$/', $r->local_id)) {
        // Find PAAH control in seed.json
        foreach ($seedData['risks'] as $sr) {
            if ($sr['localId'] === $r->local_id && $sr['areaId'] === 'PAAH' && $sr['exercise'] == $r->ejercicio_id) {
                // Insert controls if not exist
                $hasControls = DB::table('riesgo_controles')->where('riesgo_id', $r->id)->exists();
                if (!$hasControls && isset($sr['controls'])) {
                    foreach ($sr['controls'] as $c) {
                        DB::table('riesgo_controles')->insert([
                            'riesgo_id' => $r->id,
                            'texto' => $c['text'] ?? '',
                            'estado_validacion' => $c['validationStatus'] ?? 'Propuesto – pendiente de validación',
                            'evidencia_tipo' => $c['evidence']['type'] ?? '',
                            'evidencia_referencia' => $c['evidence']['reference'] ?? '',
                            'evidencia_responsable' => $c['evidence']['responsible'] ?? '',
                            'evidencia_periodicidad' => $c['evidence']['periodicity'] ?? '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $countCtrl++;
                    }
                }
                break;
            }
        }
    }
}
echo "Imported $countCtrl controls for Pleno.\n";
