<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$areas_patch = [
    'Contraloría Interna' => [
        ['numero' => '1', 'descripcion' => 'Informes trimestrales, anuales y requeridos por entes públicos'],
        ['numero' => '2', 'descripcion' => 'Investigación y procedimientos de responsabilidad administrativa'],
        ['numero' => '3', 'descripcion' => 'Prevención, responsabilidades, declaraciones patrimoniales y verificaciones'],
        ['numero' => '4', 'descripcion' => 'Auditorías, informes finales y seguimiento de observaciones'],
        ['numero' => '5', 'descripcion' => 'Instrumentar y operar la función de la Contraloría Interna como Autoridad Garante con autonomía técnica, normativa, procedimientos y herramientas suficientes.'],
        ['numero' => '6', 'descripcion' => 'Recibir, sustanciar, resolver y verificar oportunamente los procedimientos competencia de la Autoridad Garante.'],
        ['numero' => '7', 'descripcion' => 'Fortalecer la protección de datos personales y la cultura institucional de transparencia, acceso a la información y protección de datos.'],
    ],
    'Dirección General Jurídica' => [
        ['numero' => '1', 'descripcion' => 'Representación y defensa jurídica del Tribunal'],
        ['numero' => '2', 'descripcion' => 'Consultas, asesoría y opiniones jurídicas'],
        ['numero' => '3', 'descripcion' => 'Elaboración, análisis y revisión de normativa'],
        ['numero' => '4', 'descripcion' => 'Convenios, actos jurídicos y compromisos institucionales'],
    ],
    'Comunicación Social' => [
        ['numero' => '1', 'descripcion' => 'Posicionamiento y comunicación institucional'],
        ['numero' => '2', 'descripcion' => 'Relaciones públicas, cobertura y eventos institucionales'],
        ['numero' => '3', 'descripcion' => 'Elaboración y difusión de información y contenidos institucionales'],
    ],
    'Transparencia' => [ // CTyDP is usually named something like "Coordinación de Transparencia y Datos Personales"
        ['numero' => '1', 'descripcion' => 'Solicitudes de acceso a la información y recursos'],
        ['numero' => '2', 'descripcion' => 'Sesiones, acuerdos y seguimiento del Comité de Transparencia'],
        ['numero' => '3', 'descripcion' => 'Protección y tratamiento de datos personales'],
        ['numero' => '4', 'descripcion' => 'Obligaciones de transparencia, asesoría y capacitación especializada'],
    ]
];

foreach($areas_patch as $urgName => $actividades) {
    $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('urg.nombre', 'LIKE', "%$urgName%")
        ->where('ej.ejercicio', 2027)
        ->select('urg.*')
        ->first();

    if(!$urg) {
        echo "URG no encontrada para: $urgName\n";
        continue;
    }
    echo "Procesando URG: " . $urg->nombre . "\n";

    $proyecto = DB::connection('poa_prod')->table('proyectos as p')
        ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
        ->select('p.*')
        ->first();

    if(!$proyecto) {
        echo "Proyecto no encontrado para URG: " . $urg->nombre . "\n";
        continue;
    }

    DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();
    DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();

    foreach($actividades as $act) {
        DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
            'proyecto_id' => $proyecto->proyecto_id,
            'numero' => $act['numero'],
            'descripcion' => $act['descripcion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
        
        DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
            'proyecto_id' => $proyecto->proyecto_id,
            'numero' => $act['numero'],
            'descripcion' => $act['descripcion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
    }
    echo "Insertadas " . count($actividades) . " actividades para " . $urg->nombre . ".\n";
}
echo "Batch completado.\n";
