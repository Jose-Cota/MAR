<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $roId = 984; // Transparencia 2027
    $ejercicioId = 19;
    
    // Create new project
    $newProjectId = DB::table('proyectos')->insertGetId([
        'responsable_operativo_id' => $roId,
        'ejercicio_id' => $ejercicioId,
        'subprograma_id' => 1,
        'numero' => '01',
        'nombre' => 'POA 2027 – Coordinación de Transparencia y Datos Personales',
        'tipo' => 'normal',
        'version' => 1,
        'objetivo' => '',
        'justificacion' => '',
        'descripcion' => 'POA 2027 – Coordinación de Transparencia y Datos Personales',
        'fecha' => '2027-01-01',
        'status' => 'abierto',
        'nombre_responsable_operativo' => '',
        'cargo_responsable_operativo' => '',
        'nombre_titular' => '',
        'responsable_ficha' => '',
        'autorizado_por' => ''
    ]);
    echo "Created new project $newProjectId.\n";
    
    // Create 4 actions
    $actionTexts = [
        1 => 'Solicitudes de acceso a la información y recursos',
        2 => 'Sesiones, acuerdos y seguimiento del Comité de Transparencia',
        3 => 'Protección y tratamiento de datos personales',
        4 => 'Obligaciones de transparencia, asesoría y capacitación especializada'
    ];
    
    $actionIdsCreated = [];
    foreach ($actionTexts as $num => $text) {
        $actionIdsCreated[$num] = DB::table('acciones_sustantivas')->insertGetId([
            'proyecto_id' => $newProjectId,
            'numero' => $num,
            'descripcion' => $text
        ]);
    }
    echo "Created 4 actions.\n";
    
    // Get risks for Area 10 in 2027
    // Area 10 is CTyDP
    $risks = DB::table('riesgos')->where('area_id', 10)->where('ejercicio_id', $ejercicioId)->get()->keyBy('local_id');
    
    // R1
    if (isset($risks['R1'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[1], 'riesgo_id' => $risks['R1']->id]);
    }
    // R2, R4
    if (isset($risks['R2'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[2], 'riesgo_id' => $risks['R2']->id]);
    }
    if (isset($risks['R4'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[2], 'riesgo_id' => $risks['R4']->id]);
    }
    // R3, R5
    if (isset($risks['R3'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[3], 'riesgo_id' => $risks['R3']->id]);
    }
    if (isset($risks['R5'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[3], 'riesgo_id' => $risks['R5']->id]);
    }
    // R5, R4
    if (isset($risks['R5'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[4], 'riesgo_id' => $risks['R5']->id]);
    }
    if (isset($risks['R4'])) {
        DB::table('actividad_riesgo')->insert(['actividad_sustantiva_id' => $actionIdsCreated[4], 'riesgo_id' => $risks['R4']->id]);
    }
    
    echo "Linked risks to new actions.\n";
});
