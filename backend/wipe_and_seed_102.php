<?php
Schema::disableForeignKeyConstraints();
DB::table('riesgo_indicadores')->truncate();
DB::table('riesgo_controles')->truncate();
DB::table('riesgos')->truncate();
DB::table('unidades_responsables_gastos')->truncate(); // TRUNCATE completely!
Schema::enableForeignKeyConstraints();

$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
$html = file_get_contents('C:/Cota/MAR/Sistema_MAR_TECDMX_2026_2027_v5_1.html');

$def = [];
if (preg_match('/const DEF=({.*?});/', $html, $m)) {
    $def = json_decode($m[1], true);
}

// 1. Insert ALL 25 areas exactly as they are in seed.json, with unique numero
$index = 1;
$jsonAreaMap = []; // id => new DB id
foreach ($seedData['areas'] as $a) {
    $name = trim($a['name']);
    $numero = sprintf('%02d', $index);
    if (isset($a['displayCode']) && $a['displayCode']) {
        $numero = $a['displayCode'];
    }
    
    // Fallback if duplicate numero generated
    $exists = DB::table('unidades_responsables_gastos')->where('numero', $numero)->exists();
    if ($exists) {
        $numero = 'U' . sprintf('%02d', $index);
    }

    $dbId = DB::table('unidades_responsables_gastos')->insertGetId([
        'ejercicio_id' => 19, // 2027
        'numero' => $numero, 
        'nombre' => $name,
        'repite_proyecto' => 'no',
        'cerrada' => '0',
    ]);
    
    $jsonAreaMap[$a['id']] = $dbId;
    $index++;
}

$ej_id_2027 = 19;
$riskCount = 0;
$controlCount = 0;
$indicatorCount = 0;

$riskMap = []; // localId => new db id
$jsonAreaIdMap = []; // db id => jsonAreaId

// 2. Insert Risks
foreach ($seedData['risks'] as $r) {
    if ($r['exercise'] != 2026) {
        continue; // Only take the 102 risks from 2026
    }
    
    $urg_id = $jsonAreaMap[$r['areaId']] ?? null;

    if (!$urg_id) {
        echo "URG not found for areaId: {$r['areaId']}\n";
        continue;
    }

    $newRiskId = DB::table('riesgos')->insertGetId([
        'ejercicio_id' => $ej_id_2027, // Map to 2027
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

    $riskMap[$r['localId']] = $newRiskId;
    $jsonAreaIdMap[$newRiskId] = $r['areaId'];
    $riskCount++;
}

// 3. Controls
foreach ($seedData['risks'] as $sr) {
    if ($sr['exercise'] != 2026) continue;
    if (isset($riskMap[$sr['localId']]) && isset($sr['controls'])) {
        $riskId = $riskMap[$sr['localId']];
        foreach ($sr['controls'] as $c) {
            DB::table('riesgo_controles')->insert([
                'riesgo_id' => $riskId,
                'texto' => $c['text'] ?? '',
                'estado_validacion' => $c['validationStatus'] ?? 'Propuesto – pendiente de validación',
                'evidencia_tipo' => $c['evidence']['type'] ?? '',
                'evidencia_referencia' => $c['evidence']['reference'] ?? '',
                'evidencia_responsable' => $c['evidence']['responsible'] ?? '',
                'evidencia_periodicidad' => $c['evidence']['periodicity'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $controlCount++;
        }
    }
}

// 4. Indicators from DEF (html mockup)
$riesgos = DB::table('riesgos')->get();
foreach ($riesgos as $r) {
    $jsonAreaId = $jsonAreaIdMap[$r->id] ?? null;
    if (!$jsonAreaId) continue;
    
    $rk = preg_match('/R\d+$/', $r->local_id, $match) ? $match[0] : null;
    if (!$rk) continue;
    
    // Construct key
    $key = "{$jsonAreaId}-2026-{$rk}";
    
    $indData = $def[$key] ?? null;
    if ($indData) {
        $nLabel = $indData['numeratorLabel'] ?? '';
        $dLabel = $indData['denominatorLabel'] ?? '';
        DB::table('riesgo_indicadores')->insert([
            'riesgo_id' => $r->id,
            'nombre' => $indData['name'] ?? '',
            'periodicidad' => 'Trimestral',
            'formula' => "Resultado = ($nLabel / $dLabel) × 100",
            'unidad' => 'Porcentaje',
            'sentido' => 'Ascendente',
            'numerador' => $nLabel,
            'denominador' => $dLabel,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $indicatorCount++;
    }
}

echo "Wiped database and seeded EXACTLY $index areas, $riskCount risks to 2027, $controlCount controls, and $indicatorCount indicators.\n";
