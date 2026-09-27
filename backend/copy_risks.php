<?php
use Illuminate\Support\Facades\DB;

$areaId = 'USI'; // based on what we saw in the JSON file
$riesgos2027 = DB::table('riesgos')->where('ejercicio_id', 2027)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2027->count() . " risks for 2027 in area $areaId.\n";

$riesgos2026 = DB::table('riesgos')->where('ejercicio_id', 2026)->where('area_id', $areaId)->get();
echo "Found " . $riesgos2026->count() . " risks for 2026 in area $areaId.\n";

if ($riesgos2027->count() > 0) {
    // Delete existing 2026 risks to overwrite
    // Wait, first we need to handle controls, indicators, etc.
    // Let's get the risk IDs to clean up first.
    $oldRiskIds = $riesgos2026->pluck('id')->toArray();
    
    // Delete relationships
    DB::table('riesgo_controles')->whereIn('riesgo_id', $oldRiskIds)->delete();
    DB::table('riesgo_indicadores')->whereIn('riesgo_id', $oldRiskIds)->delete();
    
    // Delete old risks
    DB::table('riesgos')->whereIn('id', $oldRiskIds)->delete();
    
    foreach ($riesgos2027 as $r27) {
        $oldId = $r27->id;
        $data = (array)$r27;
        unset($data['id']); // Let it auto-increment
        $data['ejercicio_id'] = 2026;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        $newRiskId = DB::table('riesgos')->insertGetId($data);
        
        // Copy controls
        $controls = DB::table('riesgo_controles')->where('riesgo_id', $oldId)->get();
        foreach ($controls as $c) {
            $cData = (array)$c;
            unset($cData['id']);
            $cData['riesgo_id'] = $newRiskId;
            $cData['created_at'] = now();
            $cData['updated_at'] = now();
            DB::table('riesgo_controles')->insert($cData);
        }
        
        // Copy indicators
        $indicators = DB::table('riesgo_indicadores')->where('riesgo_id', $oldId)->get();
        foreach ($indicators as $i) {
            $iData = (array)$i;
            unset($iData['id']);
            $iData['riesgo_id'] = $newRiskId;
            $iData['created_at'] = now();
            $iData['updated_at'] = now();
            DB::table('riesgo_indicadores')->insert($iData);
        }
    }
    
    echo "Copied successfully.\n";
} else {
    echo "Nothing to copy from 2027.\n";
}
