<?php
use Illuminate\Support\Facades\DB;

$proyecto_2026 = 884;
$proyecto_2027 = 1438;
$meta_principal_2026 = 4021;
$meta_principal_2027 = 7150;
$meta_comp_2026 = 4022;
$ind_2026 = 7179;
$ejercicio_2027 = 19;

// Func to map UM and create if missing
function getMappedUM($old_um_id, $new_ejercicio_id) {
    if (!$old_um_id) return null;
    $old_um = DB::table('unidades_medidas')->where('unidad_medida_id', $old_um_id)->first();
    if (!$old_um) return null;
    
    $new_um = DB::table('unidades_medidas')
                ->where('ejercicio_id', $new_ejercicio_id)
                ->where('numero', $old_um->numero)
                ->first();
                
    if ($new_um) {
        return $new_um->unidad_medida_id;
    } else {
        // Clone UM
        $new_um_data = (array) $old_um;
        unset($new_um_data['unidad_medida_id']);
        $new_um_data['ejercicio_id'] = $new_ejercicio_id;
        return DB::table('unidades_medidas')->insertGetId($new_um_data);
    }
}

DB::beginTransaction();
try {
    // 1. Clonar Meta Complementaria
    $m_comp = DB::table('metas')->where('meta_id', $meta_comp_2026)->first();
    if ($m_comp) {
        $new_um_meta = getMappedUM($m_comp->unidad_medida_id, $ejercicio_2027);
        $m_comp_array = (array) $m_comp;
        unset($m_comp_array['meta_id']);
        $m_comp_array['proyecto_id'] = $proyecto_2027;
        $m_comp_array['unidad_medida_id'] = $new_um_meta;
        
        $new_m_comp_id = DB::table('metas')->insertGetId($m_comp_array);
        echo "Meta complementaria clonada exitosamente con ID: $new_m_comp_id\n";
    }

    // 2. Clonar Indicador a la Meta Principal
    $ind = DB::table('indicadores')->where('indicador_id', $ind_2026)->first();
    if ($ind) {
        $new_um_ind = getMappedUM($ind->unidad_medida_id, $ejercicio_2027);
        $ind_array = (array) $ind;
        unset($ind_array['indicador_id']);
        $ind_array['proyecto_id'] = $proyecto_2027;
        $ind_array['meta_id'] = $meta_principal_2027;
        $ind_array['unidad_medida_id'] = $new_um_ind;
        
        $new_ind_id = DB::table('indicadores')->insertGetId($ind_array);
        echo "Indicador clonado exitosamente con ID: $new_ind_id\n";
    }
    
    DB::commit();
    echo "¡Proceso de clonado finalizado correctamente!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
exit;
