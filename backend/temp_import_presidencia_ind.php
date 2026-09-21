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
  }
}
';
$data = json_decode($json, true);

$presAreas = DB::table('unidades_responsables_gastos')
    ->where('nombre', 'like', '%presidencia%')
    ->pluck('unidad_responsable_gasto_id')
    ->toArray();

$countInd = 0;
$riesgos = DB::table('riesgos')->get();

foreach ($riesgos as $r) {
    if (in_array($r->area_id, $presAreas)) {
        $rk = preg_match('/R\d+$/', $r->local_id, $m) ? $m[0] : null;
        if (!$rk) continue;
        
        $indData = $data['PRES'][$rk] ?? null;
        if ($indData) {
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
}
echo "Imported $countInd indicators for Presidencia.\n";
