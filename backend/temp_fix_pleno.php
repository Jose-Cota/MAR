<?php
$json = '
{
  "PRES": {
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
  },
  "PON": {
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
  }
}
';

$data = json_decode($json, true);

// Get Pleno area IDs
$plenoAreas = DB::table('unidades_responsables_gastos')
    ->where('nombre', 'like', '%pleno%')
    ->pluck('unidad_responsable_gasto_id')
    ->toArray();

$countInd = 0;
$countCtrl = 0;

$riesgos = DB::table('riesgos')->get();
foreach ($riesgos as $r) {
    $rk = preg_match('/R\d+$/', $r->local_id, $m) ? $m[0] : null;
    if (!$rk) continue;

    $indData = null;
    $isPon = false;
    
    if (in_array($r->area_id, $plenoAreas) || preg_match('/PAAH|PJHR|POVR|PKSL|PLPJC/', $r->local_id)) {
        $indData = $data['PON'][$rk] ?? null;
        $isPon = true;
    } elseif ($r->area_id == 2 || str_contains($r->local_id, 'PRES')) { // Assuming 2 is PRES
        $indData = $data['PRES'][$rk] ?? null;
    }

    if ($indData) {
        // Only insert if not exists
        $exists = DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->first();
        if (!$exists) {
            DB::table('riesgo_indicadores')->insert([
                'riesgo_id' => $r->id,
                'nombre' => $indData['name'],
                'periodicidad' => $indData['periodicity'],
                'formula' => $indData['formula'],
                'unidad' => $indData['unit'],
                'sentido' => $indData['direction'],
                'numerador' => $indData['numeratorLabel'],
                'denominador' => $indData['denominatorLabel'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $countInd++;
        }
    }
}

// Now controls for Pleno
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
foreach ($riesgos as $r) {
    if (in_array($r->area_id, $plenoAreas) && preg_match('/^R\d+$/', $r->local_id)) {
        // It's a Pleno risk (e.g. R1)
        // Check if controls exist
        $hasControls = DB::table('riesgo_controles')->where('riesgo_id', $r->id)->exists();
        if (!$hasControls) {
            // Find PAAH control in seed.json
            $seedLocalId = 'PAAH-' . $r->ejercicio_id . '-' . $r->local_id;
            foreach ($seedData['risks'] as $sr) {
                if ($sr['localId'] === $seedLocalId) {
                    if (isset($sr['controls'])) {
                        foreach ($sr['controls'] as $c) {
                            DB::table('riesgo_controles')->insert([
                                'riesgo_id' => $r->id,
                                'control' => $c['text'] ?? '',
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
}

echo "Imported $countInd indicators and $countCtrl controls.\n";
