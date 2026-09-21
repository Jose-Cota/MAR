<?php
// Extract PRES and PON indicators and insert them.
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

// Map Area IDs
$areas = DB::table('unidades_responsables_gastos')->get();
$dbAreaMap = [];
foreach ($areas as $a) {
    $name = trim($a->nombre);
    if (strpos(strtolower($name), 'pleno') !== false) {
        $name = 'Pleno';
    }
    $dbAreaMap[strtolower($name)] = $a->unidad_responsable_gasto_id;
}
$dbAreaMap['presidencia'] = 2; 

$count = 0;
// We fetch all riesgos
$riesgos = DB::table('riesgos')->get();
foreach ($riesgos as $r) {
    // Determine if it is PRES or PON
    $parts = explode('-', $r->local_id);
    if (count($parts) < 3) continue;
    
    $areaStr = $parts[0];
    $rk = end($parts); // R1, R2, etc.
    
    $indData = null;
    if ($areaStr === 'PRES') {
        $indData = $data['PRES'][$rk] ?? null;
    } elseif (in_array($areaStr, ['PAAH','PJHR','POVR','PKSL','PLPJC'])) {
        $indData = $data['PON'][$rk] ?? null;
    }
    
    if ($indData) {
        // Delete existing for this riesgo
        DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->delete();
        
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
        $count++;
    }
}
echo "Imported $count indicators.\n";
