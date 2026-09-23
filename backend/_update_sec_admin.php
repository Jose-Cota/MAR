<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$p = DB::table('proyectos')
    ->where('ejercicio_id', 17)
    ->where('nombre', 'POA 2026 – Secretaría Administrativa')
    ->first();

if ($p) {
    // We want to update the descriptions of the two actions
    $acciones = DB::table('acciones_sustantivas')
        ->where('proyecto_id', $p->proyecto_id)
        ->orderBy('accion_sustantiva_id')
        ->get();
        
    if (count($acciones) >= 2) {
        $a1 = $acciones[0];
        $a2 = $acciones[1];
        
        DB::table('acciones_sustantivas')
            ->where('accion_sustantiva_id', $a1->accion_sustantiva_id)
            ->update([
                'descripcion' => 'Coordinación de la administración de recursos humanos, materiales y financieros'
            ]);
            
        DB::table('acciones_sustantivas')
            ->where('accion_sustantiva_id', $a2->accion_sustantiva_id)
            ->update([
                'descripcion' => 'Informes institucionales, planeación, POA, presupuesto y seguimiento de auditorías'
            ]);
            
        // Now update risks
        // Get all risks for 2026
        $r1 = DB::table('riesgos')->where('local_id', 'R1')->where('ejercicio_id', 17)->first();
        $r2 = DB::table('riesgos')->where('local_id', 'R2')->where('ejercicio_id', 17)->first();
        $r3 = DB::table('riesgos')->where('local_id', 'R3')->where('ejercicio_id', 17)->first();
        $r4 = DB::table('riesgos')->where('local_id', 'R4')->where('ejercicio_id', 17)->first();
        
        // Delete existing mappings
        DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $a1->accion_sustantiva_id)->delete();
        DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $a2->accion_sustantiva_id)->delete();
        
        // Insert new mappings for A1 (R1)
        if ($r1) {
            DB::table('actividad_riesgo')->insert([
                'actividad_sustantiva_id' => $a1->accion_sustantiva_id,
                'riesgo_id' => $r1->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // Insert new mappings for A2 (R2, R3, R4)
        if ($r2 && $r3 && $r4) {
            DB::table('actividad_riesgo')->insert([
                ['actividad_sustantiva_id' => $a2->accion_sustantiva_id, 'riesgo_id' => $r2->id, 'created_at' => now(), 'updated_at' => now()],
                ['actividad_sustantiva_id' => $a2->accion_sustantiva_id, 'riesgo_id' => $r3->id, 'created_at' => now(), 'updated_at' => now()],
                ['actividad_sustantiva_id' => $a2->accion_sustantiva_id, 'riesgo_id' => $r4->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        echo "Updated actions and risks!\n";
    }
}
