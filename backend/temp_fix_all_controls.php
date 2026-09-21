<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);

$jsonIdToName = [];
foreach ($seedData['areas'] as $a) {
    $jsonIdToName[$a['id']] = strtolower(trim($a['urName'] ?? $a['name']));
}

$urgs = DB::table('unidades_responsables_gastos')->get();
$dbAreaIdToJsonId = [];
foreach ($urgs as $u) {
    $dbName = strtolower(trim($u->nombre));
    
    // Exact match or substring match
    if (str_contains($dbName, 'pleno')) {
        $dbAreaIdToJsonId[$u->unidad_responsable_gasto_id] = 'PAAH';
    } elseif (str_contains($dbName, 'presidencia')) {
        $dbAreaIdToJsonId[$u->unidad_responsable_gasto_id] = 'PRES';
    } else {
        foreach ($jsonIdToName as $jId => $jName) {
            if ($dbName === $jName || str_contains($jName, $dbName) || str_contains($dbName, $jName)) {
                $dbAreaIdToJsonId[$u->unidad_responsable_gasto_id] = $jId;
                break;
            }
        }
    }
}

// Ensure PRES is mapped for Presidencia
// Actually, let's just force the known ones:
$dbAreaIdToJsonId[566] = 'SG'; // 2027 SG
$dbAreaIdToJsonId[22] = 'SG'; // 2026 SG
$dbAreaIdToJsonId[565] = 'PRES'; // 2027 PRES
$dbAreaIdToJsonId[2] = 'PRES'; // 2026 PRES
$dbAreaIdToJsonId[564] = 'PAAH'; // 2027 Pleno
$dbAreaIdToJsonId[1] = 'PAAH'; // 2026 Pleno

DB::table('riesgo_controles')->truncate();
$count = 0;
$riesgos = DB::table('riesgos')->get();

foreach ($riesgos as $r) {
    $jsonAreaId = $dbAreaIdToJsonId[$r->area_id] ?? null;
    if (!$jsonAreaId) continue;
    
    $rk = preg_match('/R\d+$/', $r->local_id, $match) ? $match[0] : null;
    if (!$rk) continue;

    foreach ($seedData['risks'] as $sr) {
        if ($sr['areaId'] === $jsonAreaId && $sr['localId'] === $rk && $sr['exercise'] == $r->ejercicio_id) {
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
echo "Imported $count controls securely mapped by areaId.\n";
