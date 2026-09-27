<?php
use Illuminate\Support\Facades\DB;

// UR 13
$areaId = 13;
$ej2027 = 19;
$ej2026 = 17;

echo "Found Area ID: " . $areaId . "\n";

$riesgos2027 = DB::table('riesgos')->where('ejercicio_id', $ej2027)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2027->count() . " risks for 2027 (ej $ej2027) in area $areaId.\n";

$riesgos2026 = DB::table('riesgos')->where('ejercicio_id', $ej2026)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2026->count() . " risks for 2026 (ej $ej2026) in area $areaId.\n";

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
        $data['ejercicio_id'] = $ej2026;
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
                'actividad_sustantiva_id' => $ar->actividad_sustantiva_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    echo "Copied successfully.\n";
} else {
    echo "Nothing to copy from 2027.\n";
}
