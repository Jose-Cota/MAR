<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // 1. Get the risks for CI 2026
    $risks = DB::table('riesgos')->where('area_id', 7)->where('ejercicio_id', 17)->pluck('id', 'local_id')->toArray();
    
    // Clear old links
    if (!empty($risks)) {
        DB::table('actividad_riesgo')->whereIn('riesgo_id', array_values($risks))->delete();
    }
    
    // 2. Delete old actions for project 875
    DB::table('acciones_sustantivas')->where('proyecto_id', 875)->delete();
    
    // 3. Insert new actions and link them
    $actions = [
        1 => ['text' => 'Informes trimestrales, anuales y requeridos por entes públicos', 'risks' => ['R1']],
        2 => ['text' => 'Investigación y procedimientos de responsabilidad administrativa', 'risks' => ['R2', 'R5']],
        3 => ['text' => 'Prevención, responsabilidades, declaraciones patrimoniales y verificaciones', 'risks' => ['R3', 'R5']],
        4 => ['text' => 'Auditorías, informes finales y seguimiento de observaciones', 'risks' => ['R4']],
        5 => ['text' => 'Asesoría, acompañamiento, integridad y prevención de actos de corrupción', 'risks' => ['R3']],
    ];
    
    foreach ($actions as $num => $data) {
        $actId = DB::table('acciones_sustantivas')->insertGetId([
            'proyecto_id' => 875,
            'numero' => $num,
            'descripcion' => $data['text'],
        ]);
        
        foreach ($data['risks'] as $rLocal) {
            if (isset($risks[$rLocal])) {
                DB::table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $actId,
                    'riesgo_id' => $risks[$rLocal],
                ]);
            } else {
                echo "Warning: Risk $rLocal not found\n";
            }
        }
    }
    
    DB::commit();
    echo "Successfully updated CI 2026 actions and links.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
