<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$areas_patch = [
    'Presidencia' => [
        ['numero' => '1', 'descripcion' => 'Representación oficial e institucional del Tribunal'],
        ['numero' => '2', 'descripcion' => 'Reuniones de trabajo y coordinación institucional'],
        ['numero' => '3', 'descripcion' => 'Seguimiento al cumplimiento de acuerdos y resoluciones del Pleno'],
    ],
    'Secretaría Administrativa' => [
        ['numero' => '1', 'descripcion' => 'Coordinación de la administración de recursos humanos, materiales y financieros'],
        ['numero' => '2', 'descripcion' => 'Informes institucionales, planeación, POA, presupuesto y seguimiento de auditorías'],
    ],
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
    'Transparencia' => [
        ['numero' => '1', 'descripcion' => 'Solicitudes de acceso a la información y recursos'],
        ['numero' => '2', 'descripcion' => 'Sesiones, acuerdos y seguimiento del Comité de Transparencia'],
        ['numero' => '3', 'descripcion' => 'Protección y tratamiento de datos personales'],
        ['numero' => '4', 'descripcion' => 'Obligaciones de transparencia, asesoría y capacitación especializada'],
    ]
];

// Special areas that don't have a 2027 project correctly linked yet
// DPyRF, DRH, DRMySG are missing from 'proyectos' where ejercicio_id=19
// We need to find their 2027 projects. Wait, the API returns them, so they must be in 'proyectos'.
// Let's query any project for the area regardless of ro.unidad_responsable_gasto_id if it's 2027!

$proyectos_2027 = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('p.ejercicio_id', 19) // EXPLICITLY 2027
    ->select('p.proyecto_id', 'urg.nombre')
    ->get();

foreach($areas_patch as $area => $actividades) {
    $matched_proyectos = [];
    foreach($proyectos_2027 as $p) {
        if(stripos($p->nombre, $area) !== false) {
            $matched_proyectos[] = $p;
        }
    }
    
    if(count($matched_proyectos) == 0) {
        echo "NO SE ENCONTRÓ PROYECTO 2027 PARA $area\n";
        continue;
    }
    
    // We will update ALL matched projects just to be absolutely sure the frontend picks it up.
    // Sometimes there are duplicates like Presidencia 1804, 1805, 1806, 1767.
    foreach($matched_proyectos as $p) {
        DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        
        foreach($actividades as $act) {
            DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
                'proyecto_id' => $p->proyecto_id,
                'numero' => $act['numero'],
                'descripcion' => $act['descripcion'],
                'recursos_asociados' => '',
                'es_resumida' => 1
            ]);
            
            DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
                'proyecto_id' => $p->proyecto_id,
                'numero' => $act['numero'],
                'descripcion' => $act['descripcion'],
                'recursos_asociados' => '',
                'es_resumida' => 1
            ]);
        }
        echo "Actualizado Proyecto {$p->proyecto_id} para {$p->nombre}\n";
    }
}
echo "Done 2027.\n";
