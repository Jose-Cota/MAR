<?php

$html = file_get_contents('C:/Cota/MAR/Sistema_MAR_TECDMX_2026_2027_v5_1.html');
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);

if (preg_match('/const DEF=({.*?});/', $html, $m)) {
    $def = json_decode($m[1], true);
}
// Add PRES to def
$presJson = '
{
    "R1": {
      "name": "% de acuerdos/resoluciones con seguimiento oportuno.",
      "numeratorLabel": "Acuerdos y resoluciones con seguimiento oportuno",
      "denominatorLabel": "Total de acuerdos y resoluciones sujetos a seguimiento",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R2": {
      "name": "% de reuniones con acuerdos documentados y seguimiento.",
      "numeratorLabel": "Reuniones con acuerdos documentados y seguimiento",
      "denominatorLabel": "Total de reuniones realizadas con acuerdos sujetos a seguimiento",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R3": {
      "name": "% de eventos/actos atendidos conforme a agenda y requerimientos.",
      "numeratorLabel": "Eventos y actos atendidos conforme a agenda y requerimientos",
      "denominatorLabel": "Total de eventos y actos programados o requeridos",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    }
}';
$presDef = json_decode($presJson, true);
$def['PRES-2026-R1'] = $presDef['R1'];
$def['PRES-2026-R2'] = $presDef['R2'];
$def['PRES-2026-R3'] = $presDef['R3'];
// Add PON to def
$ponJson = '
{
    "R1": {
      "name": "% de proyectos/asuntos atendidos respecto de los recibidos = (Atendido/Recibido)*100; seguimiento de incidencias sustantivas.",
      "numeratorLabel": "Atendido",
      "denominatorLabel": "Recibido",
      "formula": "Resultado = (Atendido/Recibido)*100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R2": {
      "name": "% de actuaciones de deliberación, votación, votos y engroses atendidas oportunamente y sin incidencias de integración.",
      "numeratorLabel": "Actuaciones de deliberación, votación, votos y engroses atendidas oportunamente y sin incidencias de integración",
      "denominatorLabel": "Total de actuaciones de deliberación, votación, votos y engroses sujetas a atención",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R3": {
      "name": "% de documentos y propuestas sometidos a revisión o dictaminación atendidos dentro del plazo requerido y sin observaciones sustantivas atribuibles a revisión insuficiente.",
      "numeratorLabel": "Documentos y propuestas atendidos dentro del plazo requerido y sin observaciones sustantivas atribuibles a revisión insuficiente",
      "denominatorLabel": "Total de documentos y propuestas sometidos a revisión o dictaminación",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R4": {
      "name": "% de comisiones y asuntos administrativos atendidos dentro del plazo o fecha compromiso.",
      "numeratorLabel": "Comisiones y asuntos administrativos atendidos dentro del plazo o fecha compromiso",
      "denominatorLabel": "Total de comisiones y asuntos administrativos sujetos a atención",
      "formula": "Resultado = (N / D) × 100",
      "unit": "Porcentaje",
      "periodicity": "Trimestral",
      "direction": "Ascendente"
    },
    "R5": {
      "name": "Número de incidencias documentadas relacionadas con autonomía, independencia o imparcialidad.",
      "numeratorLabel": "",
      "denominatorLabel": "",
      "formula": "Resultado = Número de incidencias documentadas relacionadas con autonomía, independencia o imparcialidad",
      "unit": "Incidencia",
      "periodicity": "Trimestral",
      "direction": "Descendente"
    }
}';
$ponDef = json_decode($ponJson, true);
$ponAreas = ['PAAH','PJHR','POVR','PKSL','PLPJC'];
foreach ($ponAreas as $pa) {
    foreach ($ponDef as $k => $v) {
        $def["$pa-2026-$k"] = $v;
    }
}


$jsonIdToName = [];
foreach ($seedData['areas'] as $a) {
    $jsonIdToName[$a['id']] = strtolower(trim($a['urName'] ?? $a['name']));
}

$urgs = DB::table('unidades_responsables_gastos')->get();
$dbAreaIdToJsonId = [];
foreach ($urgs as $u) {
    $dbName = strtolower(trim($u->nombre));
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

$dbAreaIdToJsonId[566] = 'SG';
$dbAreaIdToJsonId[22] = 'SG';
$dbAreaIdToJsonId[565] = 'PRES';
$dbAreaIdToJsonId[2] = 'PRES';
$dbAreaIdToJsonId[564] = 'PAAH';
$dbAreaIdToJsonId[1] = 'PAAH';

$count = 0;
$riesgos = DB::table('riesgos')->get();

foreach ($riesgos as $r) {
    $jsonAreaId = $dbAreaIdToJsonId[$r->area_id] ?? null;
    if (!$jsonAreaId) continue;
    
    $rk = preg_match('/R\d+$/', $r->local_id, $match) ? $match[0] : null;
    if (!$rk) continue;
    
    $key = "{$jsonAreaId}-2026-{$rk}";
    $indData = $def[$key] ?? null;
    
    if ($indData) {
        $nLabel = $indData['numeratorLabel'] ?? '';
        $dLabel = $indData['denominatorLabel'] ?? '';
        $formula = $indData['formula'] ?? "Resultado = ($nLabel / $dLabel) × 100";
        $unit = $indData['unit'] ?? 'Porcentaje';
        $periodicity = $indData['periodicity'] ?? 'Trimestral';
        $direction = $indData['direction'] ?? 'Ascendente';
        
        // Update all indicators for this risk
        DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->update([
            'nombre' => $indData['name'] ?? '',
            'periodicidad' => $periodicity,
            'formula' => $formula,
            'unidad' => $unit,
            'sentido' => $direction,
            'numerador' => $nLabel,
            'denominador' => $dLabel,
            'updated_at' => now(),
        ]);
        $count++;
    }
}
echo "Updated $count indicator rows.\n";
