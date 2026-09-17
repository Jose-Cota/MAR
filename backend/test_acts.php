<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach([884, 1438] as $pid) {
    echo "--- Proyecto $pid ---\n";
    $ej = DB::connection('poa_prod')->table('proyectos as py')
        ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
        ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
        ->join('ejercicios as ej', 'pg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('py.proyecto_id', $pid)
        ->select('ej.ejercicio')->first();
    echo "Ejercicio: " . json_encode($ej) . "\n";
    
    $acts = DB::table('actividades_sustantivas')->where('proyecto_id', $pid)->get();
    echo "Actividades: " . json_encode($acts) . "\n";
    
    $pei = DB::table('pei_proyecto_alineaciones')->where('proyecto_id', $pid)->first();
    echo "Alineacion PEI: " . json_encode($pei) . "\n";
}
