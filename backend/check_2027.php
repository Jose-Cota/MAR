<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
    if (!$ej2027) {
        echo "NO SE ENCONTRÓ EJERCICIO 2027\n";
        return;
    }
    
    $ejId = $ej2027->ejercicio_id;
    echo "Ejercicio 2027 ID: $ejId\n";
    
    $urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $ejId)->count();
    $ros = DB::connection('poa_prod')->table('responsables_operativos')
        ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
        ->where('unidades_responsables_gastos.ejercicio_id', $ejId)->count();
    $pgs = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', $ejId)->count();
    $sps = DB::connection('poa_prod')->table('subprogramas')
        ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
        ->where('programas.ejercicio_id', $ejId)->count();
    
    // Proyectos
    $pys = DB::connection('poa_prod')->table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
        ->where('unidades_responsables_gastos.ejercicio_id', $ejId)->count();
        
    echo "URGs: $urgs\n";
    echo "ROs: $ros\n";
    echo "PGs: $pgs\n";
    echo "SPs: $sps\n";
    echo "Proyectos: $pys\n";
    
    $pei = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2027)->first();
    echo "PEI Programa 2027: " . ($pei ? "Existe" : "No existe") . "\n";
    
    // Ultimos errores en logs
    echo "\n--- LARAVEL LOG TAIL ---\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $lines = file($logPath);
        $tail = array_slice($lines, -30);
        echo implode("", $tail);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
