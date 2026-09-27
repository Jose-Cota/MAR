<?php
use Illuminate\Support\Facades\DB;

$areas = DB::table('unidades_responsables_gastos')
    ->where('area', 'like', '%Informáticos%')
    ->orWhere('nombre', 'like', '%Informáticos%')
    ->get();

if ($areas->isEmpty()) {
    echo "Area not found in unidades_responsables_gastos.\n";
    
    // Maybe checking areas table?
    $areas = DB::table('areas')
        ->where('nombre', 'like', '%Informáticos%')
        ->get();
    
    if ($areas->isEmpty()) {
        echo "Area not found in areas table either.\n";
        exit;
    }
}

$areaId = $areas->first()->id_unidad ?? $areas->first()->id;
echo "Found Area ID: " . $areaId . "\n";

$riesgos2027 = DB::table('riesgos')->where('ejercicio_id', 2027)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2027->count() . " risks for 2027 in area $areaId.\n";

$riesgos2026 = DB::table('riesgos')->where('ejercicio_id', 2026)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2026->count() . " risks for 2026 in area $areaId.\n";

if ($riesgos2027->count() > 0) {
    $oldRiskIds = $riesgos2026->pluck('id')->toArray();
    
    if (count($oldRiskIds) > 0) {
        DB::table('riesgo_controles')->whereIn('riesgo_id', $oldRiskIds)->delete();
        DB::table('riesgo_indicadores')->whereIn('riesgo_id', $oldRiskIds)->delete();
        DB::table('actividad_riesgo')->whereIn('riesgo_id', $oldRiskIds)->delete();
        DB::table('riesgos')->whereIn('id', $oldRiskIds)->delete();
        echo "Deleted " . count($oldRiskIds) . " old risks for 2026.\n";
    }
    
    foreach ($riesgos2027 as $r27) {
        $oldId = $r27->id;
        $data = (array)$r27;
        unset($data['id']); 
        $data['ejercicio_id'] = 2026;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        $newRiskId = DB::table('riesgos')->insertGetId($data);
        
        $controls = DB::table('riesgo_controles')->where('riesgo_id', $oldId)->get();
        foreach ($controls as $c) {
            $cData = (array)$c;
            unset($cData['id']);
            $cData['riesgo_id'] = $newRiskId;
            $cData['created_at'] = now();
            $cData['updated_at'] = now();
            DB::table('riesgo_controles')->insert($cData);
        }
        
        $indicators = DB::table('riesgo_indicadores')->where('riesgo_id', $oldId)->get();
        foreach ($indicators as $i) {
            $iData = (array)$i;
            unset($iData['id']);
            $iData['riesgo_id'] = $newRiskId;
            $iData['created_at'] = now();
            $iData['updated_at'] = now();
            DB::table('riesgo_indicadores')->insert($iData);
        }
        
        $actRiesgo = DB::table('actividad_riesgo')->where('riesgo_id', $oldId)->get();
        foreach ($actRiesgo as $ar) {
            DB::table('actividad_riesgo')->insert([
                'riesgo_id' => $newRiskId,
                'actividad_id' => $ar->actividad_id
            ]);
        }
    }
    
    echo "Copied successfully.\n";
} else {
    echo "Nothing to copy from 2027.\n";
}
