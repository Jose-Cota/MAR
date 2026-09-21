<?php

$seedJson = file_get_contents('C:/Cota/MAR/backend/seed.json');
$seed = json_decode($seedJson, true);

if (!$seed || !isset($seed['risks'])) {
    echo "Failed to parse SEED or no risks found.\n";
    exit;
}

$areas = DB::table('unidades_responsables_gastos')->get();
$dbAreaMap = []; // Name => urg_id
foreach ($areas as $a) {
    $name = trim($a->nombre);
    if (strpos(strtolower($name), 'pleno') !== false) {
        $name = 'Pleno';
    }
    $dbAreaMap[strtolower($name)] = $a->unidad_responsable_gasto_id;
}

$jsonAreaMap = []; // json areaId => json Name
foreach ($seed['areas'] as $a) {
    $name = trim($a['urName'] ?? $a['name']);
    if (strpos(strtolower($name), 'pleno') !== false) {
        $name = 'Pleno';
    }
    $jsonAreaMap[$a['id']] = $name;
}

$count = 0;
foreach ($seed['risks'] as $r) {
    $ejercicio_anio = $r['exercise'];
    $ejercicio_record = DB::table('ejercicios')->where('ejercicio', $ejercicio_anio)->first();
    $ejercicio_id = $ejercicio_record ? $ejercicio_record->ejercicio_id : ($ejercicio_anio == 2027 ? 19 : 17);

    $areaName = $jsonAreaMap[$r['areaId']] ?? '';
    $urg_id = $dbAreaMap[strtolower($areaName)] ?? null;

    if (!$urg_id) {
        echo "URG not found for area: $areaName ({$r['areaId']})\n";
        continue;
    }

    // Check if exists
    $exists = DB::table('riesgos')
        ->where('ejercicio_id', $ejercicio_id)
        ->where('area_id', $urg_id)
        ->where('local_id', $r['localId'])
        ->first();

    if (!$exists) {
        DB::table('riesgos')->insert([
            'ejercicio_id' => $ejercicio_id,
            'area_id' => $urg_id,
            'local_id' => $r['localId'],
            'objetivo' => $r['objective'] ?? '',
            'riesgo' => $r['risk'] ?? '',
            'factores_internos' => $r['factors'] ?? '',
            'probabilidad' => $r['probability'] ?? 2,
            'impacto' => $r['impact'] ?? 8,
            'status' => $r['status'] ?? 'Borrador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $count++;
    }
}

echo "Imported $count new risks from SEED JSON.\n";
