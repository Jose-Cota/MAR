<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proy1 = 1792;
$proy2 = 1793;
$areaId = 14;
$ejercicioId = 19;

DB::beginTransaction();
try {
    // 1. Delete existing links in actividad_riesgo
    $riesgos = DB::table('riesgos')->where('area_id', $areaId)->where('ejercicio_id', $ejercicioId)->get();
    foreach ($riesgos as $r) {
        DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->delete();
    }
    
    // 2. Delete existing acciones_sustantivas
    DB::table('acciones_sustantivas')->whereIn('proyecto_id', [$proy1, $proy2])->delete();
    
    // 3. Insert new short actions
    $a1_id = DB::table('acciones_sustantivas')->insertGetId([
        'proyecto_id' => $proy1,
        'numero' => 1,
        'descripcion' => 'Elaboración, revisión y aprobación de anteproyectos y reglas de Jurisprudencia y Tesis.',
        'recursos_asociados' => null
    ]);
    
    $a2_id = DB::table('acciones_sustantivas')->insertGetId([
        'proyecto_id' => $proy1,
        'numero' => 2,
        'descripcion' => 'Registro, compilación, sistematización y difusión de jurisprudencia y resoluciones.',
        'recursos_asociados' => null
    ]);
    
    $a3_id = DB::table('acciones_sustantivas')->insertGetId([
        'proyecto_id' => $proy2,
        'numero' => 1,
        'descripcion' => 'Registro, sistematización y elaboración de reportes de estadística jurisdiccional.',
        'recursos_asociados' => null
    ]);
    
    // 4. Link risks to new actions
    $r1 = DB::table('riesgos')->where('area_id', $areaId)->where('ejercicio_id', $ejercicioId)->where('local_id', 'R1')->first();
    $r2 = DB::table('riesgos')->where('area_id', $areaId)->where('ejercicio_id', $ejercicioId)->where('local_id', 'R2')->first();
    $r3 = DB::table('riesgos')->where('area_id', $areaId)->where('ejercicio_id', $ejercicioId)->where('local_id', 'R3')->first();
    
    if ($r1) {
        DB::table('actividad_riesgo')->insert(['riesgo_id' => $r1->id, 'actividad_sustantiva_id' => $a1_id]);
    }
    if ($r2) {
        DB::table('actividad_riesgo')->insert(['riesgo_id' => $r2->id, 'actividad_sustantiva_id' => $a2_id]);
    }
    if ($r3) {
        DB::table('actividad_riesgo')->insert(['riesgo_id' => $r3->id, 'actividad_sustantiva_id' => $a3_id]);
    }
    
    DB::commit();
    echo "Exito. Acciones insertadas y vinculadas.";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage();
}
