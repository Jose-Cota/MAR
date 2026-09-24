<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$matrix = [
    [
        'urg_like' => 'Presidencia',
        'area_id' => 1,
        'actividades' => [
            ['numero' => '1', 'descripcion' => 'Representación oficial e institucional del Tribunal', 'riesgos' => ['R3']],
            ['numero' => '2', 'descripcion' => 'Reuniones de trabajo y coordinación institucional', 'riesgos' => ['R2']],
            ['numero' => '3', 'descripcion' => 'Seguimiento al cumplimiento de acuerdos y resoluciones del Pleno', 'riesgos' => ['R1']],
        ]
    ],
    [
        'urg_like' => 'Secretaría General',
        'area_id' => 2,
        'actividades' => [
            ['numero' => '1', 'descripcion' => 'Recepción, registro, turno e integración de expedientes jurisdiccionales', 'riesgos' => ['R1', 'R2', 'R3', 'R4']],
            ['numero' => '2', 'descripcion' => 'Diligencias, notificaciones y actuaciones ordenadas', 'riesgos' => ['R2', 'R3']],
            ['numero' => '3', 'descripcion' => 'Apoyo técnico-jurídico al Pleno, sesiones, actas, acuerdos y certificaciones', 'riesgos' => ['R3', 'R4']],
        ]
    ],
    [
        'urg_like' => 'Secretaría Administrativa',
        'area_id' => 3,
        'actividades' => [
            ['numero' => '1', 'descripcion' => 'Coordinación de la administración de recursos humanos, materiales y financieros', 'riesgos' => ['R1', 'R2']],
            ['numero' => '2', 'descripcion' => 'Informes institucionales, planeación, POA, presupuesto y seguimiento de auditorías', 'riesgos' => ['R1', 'R2', 'R3', 'R4']],
        ]
    ]
];

foreach ($matrix as $data) {
    echo "Procesando {$data['urg_like']} (Area ID {$data['area_id']})...\n";
    
    // Obtener proyectos 2027
    $proyectos = DB::connection('poa_prod')->table('proyectos as p')
        ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->where('p.ejercicio_id', 19)
        ->where('urg.nombre', 'LIKE', '%' . $data['urg_like'] . '%')
        ->select('p.proyecto_id')
        ->get();

    // Obtener riesgos 2027 de esta área
    $riesgos_db = DB::connection('poa_prod')->table('riesgos')
        ->where('ejercicio_id', 19)
        ->where('area_id', $data['area_id'])
        ->get();
        
    echo "  Riesgos encontrados: " . count($riesgos_db) . "\n";

    foreach ($proyectos as $p) {
        // Limpiar actividades viejas
        DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        
        foreach ($data['actividades'] as $act) {
            $id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
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
            
            // Ligar riesgos
            foreach ($act['riesgos'] as $r_local) {
                $riesgo = $riesgos_db->where('local_id', $r_local)->first();
                if ($riesgo) {
                    DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                        'actividad_sustantiva_id' => $id,
                        'riesgo_id' => $riesgo->id
                    ]);
                }
            }
        }
    }
    echo "  Completado {$data['urg_like']}!\n";
}
echo "PROCESO TOTAL COMPLETADO.\n";
