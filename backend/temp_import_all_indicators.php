<?php

$html = file_get_contents('C:/Cota/MAR/Sistema_MAR_TECDMX_2026_2027_v5_1.html');
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);

if (preg_match('/const DEF=({.*?});/', $html, $m)) {
    $def = json_decode($m[1], true);
    if (!$def) {
        echo "Failed to parse DEF JSON\n";
        exit;
    }
    
    // Create map from ur_name to areaId
    $areaNameToId = [];
    foreach ($seedData['areas'] as $a) {
        $n = trim($a['urName'] ?? $a['name']);
        if (str_contains(strtolower($n), 'pleno')) $n = 'Pleno';
        $areaNameToId[strtolower($n)] = $a['id'];
    }
    
    // Create map from DB area_id to JSON areaId
    $dbAreaIdToJsonId = [];
    $urgs = DB::table('unidades_responsables_gastos')->get();
    foreach ($urgs as $u) {
        $n = strtolower(trim($u->nombre));
        if (str_contains($n, 'pleno')) $n = 'pleno';
        if (isset($areaNameToId[$n])) {
            $dbAreaIdToJsonId[$u->unidad_responsable_gasto_id] = $areaNameToId[$n];
        }
    }
    
    $count = 0;
    $riesgos = DB::table('riesgos')->get();
    
    foreach ($riesgos as $r) {
        // Pleno was already handled, but let's just make it robust
        $jsonAreaId = $dbAreaIdToJsonId[$r->area_id] ?? null;
        if (!$jsonAreaId) continue;
        
        $rk = preg_match('/R\d+$/', $r->local_id, $match) ? $match[0] : null;
        if (!$rk) continue;
        
        // Construct the key like SG-2026-R1
        $key = "{$jsonAreaId}-2026-{$rk}";
        
        $indData = $def[$key] ?? null;
        
        if ($indData) {
            $exists = DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->first();
            if (!$exists) {
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
                $count++;
            }
        }
    }
    echo "Imported $count indicators from DEF.\n";
} else {
    echo "DEF not found.\n";
}

