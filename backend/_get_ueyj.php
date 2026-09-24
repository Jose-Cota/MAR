<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$area = Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->where('nombre', 'like', '%Estad%stica y Jurisprudencia%')->first();
if ($area) {
    echo "Area: " . $area->nombre . "\n";
    $areaId = $area->id_unidad ?? $area->unidad_responsable_gasto_id;
    echo "Area ID: " . $areaId . "\n";
    
    $proyectos2026 = Illuminate\Support\Facades\DB::table('proyectos')->where('ejercicio_id', 18)->get();
    foreach ($proyectos2026 as $p) {
        if (stripos($p->nombre, 'Estad') !== false || stripos($p->nombre, 'Jurisprudencia') !== false) {
            echo "Proy 2026: {$p->proyecto_id} - {$p->nombre}\n";
            $acciones2026 = Illuminate\Support\Facades\DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            echo "\nAcciones 2026:\n";
            foreach ($acciones2026 as $a) {
                echo " - [{$a->accion_sustantiva_id}] {$a->descripcion}\n";
            }
        }
    }
    
    $proyectos2027 = Illuminate\Support\Facades\DB::table('proyectos')->where('ejercicio_id', 19)->get();
    foreach ($proyectos2027 as $p) {
        if (stripos($p->nombre, 'Estad') !== false || stripos($p->nombre, 'Jurisprudencia') !== false) {
            echo "\nProy 2027: {$p->proyecto_id} - {$p->nombre}\n";
            $acciones2027 = Illuminate\Support\Facades\DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            echo "\nAcciones 2027 (actuales largas):\n";
            foreach ($acciones2027 as $a) {
                echo " - [{$a->accion_sustantiva_id}] {$a->descripcion}\n";
            }
        }
    }
    
    $riesgos2027 = Illuminate\Support\Facades\DB::table('riesgos')->where('ejercicio_id', 19)->where('area_id', $areaId)->get();
    echo "\nRiesgos 2027 (area_id = $areaId):\n";
    foreach ($riesgos2027 as $r) {
        echo " - {$r->local_id}: {$r->riesgo}\n";
    }
}
