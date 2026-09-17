<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py_2026_id = 867;
$py_2027_id = 1421;
$ej_2027_id = 19; // Assuming from check_metas_data.php that 2027 is 19. Let's get it dynamically.

$ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
$ej_2027_id = $ej2027->ejercicio_id;

function getMappedUM($old_um_id, $new_ejercicio_id) {
    if (!$old_um_id) return null;
    $old_um = DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', $old_um_id)->first();
    if (!$old_um) return null;
    
    $new_um = DB::connection('poa_prod')->table('unidades_medidas')
                ->where('ejercicio_id', $new_ejercicio_id)
                ->where('numero', $old_um->numero)
                ->first();
                
    return $new_um ? $new_um->unidad_medida_id : null;
}

DB::beginTransaction();
try {
    echo "Iniciando clonacion de detalles del proyecto $py_2026_id a $py_2027_id\n";
    
    // 1. Acciones Sustantivas
    $acciones = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $py_2026_id)->get();
    foreach ($acciones as $a) {
        $a_arr = (array)$a;
        unset($a_arr['accion_sustantiva_id']);
        $a_arr['proyecto_id'] = $py_2027_id;
        DB::connection('poa_prod')->table('acciones_sustantivas')->insert($a_arr);
    }
    echo "Clonadas " . count($acciones) . " acciones_sustantivas.\n";
    
    // 2. Metas (guardamos el mapeo de old_id => new_id)
    $metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $py_2026_id)->orderBy('meta_id')->get();
    $meta_map = [];
    
    // Primero insertamos y mapeamos
    foreach ($metas as $m) {
        $m_arr = (array)$m;
        $old_id = $m_arr['meta_id'];
        unset($m_arr['meta_id']);
        $m_arr['proyecto_id'] = $py_2027_id;
        if ($m_arr['unidad_medida_id']) {
            $m_arr['unidad_medida_id'] = getMappedUM($m_arr['unidad_medida_id'], $ej_2027_id) ?: $m_arr['unidad_medida_id'];
        }
        
        $new_id = DB::connection('poa_prod')->table('metas')->insertGetId($m_arr);
        $meta_map[$old_id] = $new_id;
    }
    
    // Luego actualizamos los meta_padre_id si existen
    foreach ($metas as $m) {
        if ($m->meta_padre_id && isset($meta_map[$m->meta_padre_id])) {
            $new_padre_id = $meta_map[$m->meta_padre_id];
            DB::connection('poa_prod')->table('metas')
                ->where('meta_id', $meta_map[$m->meta_id])
                ->update(['meta_padre_id' => $new_padre_id]);
        }
    }
    echo "Clonadas " . count($metas) . " metas.\n";
    
    // 3. Indicadores
    $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $py_2026_id)->get();
    foreach ($inds as $i) {
        $i_arr = (array)$i;
        unset($i_arr['indicador_id']);
        $i_arr['proyecto_id'] = $py_2027_id;
        
        if ($i_arr['unidad_medida_id']) {
            $i_arr['unidad_medida_id'] = getMappedUM($i_arr['unidad_medida_id'], $ej_2027_id) ?: $i_arr['unidad_medida_id'];
        }
        
        if ($i_arr['meta_id'] && isset($meta_map[$i_arr['meta_id']])) {
            $i_arr['meta_id'] = $meta_map[$i_arr['meta_id']];
        }
        if ($i_arr['id_metap'] && isset($meta_map[$i_arr['id_metap']])) {
            $i_arr['id_metap'] = $meta_map[$i_arr['id_metap']];
        }
        if ($i_arr['id_metac'] && isset($meta_map[$i_arr['id_metac']])) {
            $i_arr['id_metac'] = $meta_map[$i_arr['id_metac']];
        }
        
        DB::connection('poa_prod')->table('indicadores')->insert($i_arr);
    }
    echo "Clonados " . count($inds) . " indicadores.\n";
    
    DB::commit();
    echo "¡Proceso de clonado finalizado correctamente!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
