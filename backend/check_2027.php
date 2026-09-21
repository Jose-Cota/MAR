<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Ejercicio 2027 = ejercicio_id?
$ej2027 = DB::table('ejercicios')->where('ejercicio', 2027)->first();
echo "Ejercicio 2027: " . json_encode($ej2027) . "\n\n";

if ($ej2027) {
    $proyectos = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', $ej2027->ejercicio_id)
        ->select('proyectos.proyecto_id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id')
        ->take(5)
        ->get();

    echo "Sample proyectos for 2027 (ej_id=" . $ej2027->ejercicio_id . "):\n";
    print_r($proyectos->toArray());
    
    // Check if area_id=20 (Unidad Especializada) has projects
    $count20 = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', $ej2027->ejercicio_id)
        ->where('responsables_operativos.unidad_responsable_gasto_id', 20)
        ->count();
    echo "\nProyectos for URG 20 in 2027: $count20\n";
    
    // Check actions
    if ($count20 > 0) {
        $p = DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->where('proyectos.ejercicio_id', $ej2027->ejercicio_id)
            ->where('responsables_operativos.unidad_responsable_gasto_id', 20)
            ->select('proyectos.proyecto_id')
            ->first();
        $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->count();
        echo "Acciones for proyecto {$p->proyecto_id}: $acts\n";
    }
}
