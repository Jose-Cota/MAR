<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
$riesgos = DB::table('riesgos')->get();
$count = 0;

foreach ($riesgos as $r) {
    // We already imported Pleno controls in a previous script, but let's just make it robust.
    // If it already has controls, skip.
    $hasControls = DB::table('riesgo_controles')->where('riesgo_id', $r->id)->exists();
    if ($hasControls) continue;

    // The seed.json localId might be exactly $r->local_id
    // Wait, some $r->local_id are "R1" for Pleno, but we already skipped them since they have controls now.
    
    foreach ($seedData['risks'] as $sr) {
        if ($sr['localId'] === $r->local_id && $sr['exercise'] == $r->ejercicio_id) {
            if (isset($sr['controls'])) {
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
                    $count++;
                }
            }
            break;
        }
    }
}

echo "Imported $count controls for other areas.\n";
