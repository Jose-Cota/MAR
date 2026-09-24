<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('p.ejercicio_id', 19)
    ->where('urg.nombre', 'LIKE', '%Secretaría General%')
    ->select('p.proyecto_id', 'urg.unidad_responsable_gasto_id', 'urg.nombre')
    ->get();

foreach($proyectos as $p) {
    echo "Procesando Proyecto {$p->proyecto_id} ({$p->nombre})...\n";
    
    // Delete old ones
    DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
    DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
    
    $actividades = [
        ['numero' => '1', 'descripcion' => 'Recepción, registro, turno e integración de expedientes jurisdiccionales', 'riesgos' => ['R1', 'R2', 'R3', 'R4']],
        ['numero' => '2', 'descripcion' => 'Diligencias, notificaciones y actuaciones ordenadas', 'riesgos' => ['R2', 'R3']],
        ['numero' => '3', 'descripcion' => 'Apoyo técnico-jurídico al Pleno, sesiones, actas, acuerdos y certificaciones', 'riesgos' => ['R3', 'R4']],
    ];
    
    $riesgos_db = DB::connection('poa_prod')->table('riesgos')
        ->where('ejercicio_id', 19)
        ->where('area_id', 3) // SG catalog ID is usually 3, let's verify
        ->get();
        
    // Wait, let's make sure we find risks for area 3. If not, maybe area_id = urg.unidad_responsable_gasto_id
    if (count($riesgos_db) == 0) {
        // Try with urg_id directly
        $riesgos_db = DB::connection('poa_prod')->table('riesgos')
            ->where('ejercicio_id', 19)
            ->where('area_id', $p->unidad_responsable_gasto_id)
            ->get();
    }
    
    echo "Riesgos encontrados: " . count($riesgos_db) . "\n";
    
    foreach($actividades as $act) {
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
        
        foreach($act['riesgos'] as $r_local_id) {
            $riesgo = $riesgos_db->where('local_id', $r_local_id)->first();
            if($riesgo) {
                DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $id,
                    'riesgo_id' => $riesgo->id
                ]);
                echo "Ligado Actividad {$id} a Riesgo {$riesgo->local_id} ({$riesgo->id})\n";
            } else {
                echo "Riesgo $r_local_id no encontrado en la DB!\n";
            }
        }
    }
}
echo "Done.\n";
