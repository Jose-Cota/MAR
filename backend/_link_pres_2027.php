<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('p.ejercicio_id', 19)
    ->where('urg.nombre', 'LIKE', '%Presidencia%')
    ->select('p.proyecto_id', 'urg.unidad_responsable_gasto_id')
    ->get();

foreach($proyectos as $p) {
    echo "Procesando Proyecto {$p->proyecto_id}...\n";
    
    // Find risks for this area in 2027
    $riesgos = DB::connection('poa_prod')->table('riesgos')
        ->where('ejercicio_id', 19)
        ->where('area_id', 1) // Presidencia catalog ID is 1
        ->get();
        
    echo "Riesgos encontrados: " . count($riesgos) . "\n";
    
    $actividades = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    
    // Matriz de Presidencia:
    // A1 -> R3
    // A2 -> R2
    // A3 -> R1
    
    foreach($actividades as $act) {
        $r_local_id = '';
        if(strpos($act->descripcion, 'Representación') !== false) $r_local_id = 'R3';
        if(strpos($act->descripcion, 'Reuniones') !== false) $r_local_id = 'R2';
        if(strpos($act->descripcion, 'Seguimiento') !== false) $r_local_id = 'R1';
        
        if($r_local_id) {
            $riesgo = $riesgos->where('local_id', $r_local_id)->first();
            if($riesgo) {
                DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $act->id,
                    'riesgo_id' => $riesgo->id
                ]);
                echo "Ligado {$act->id} a Riesgo {$riesgo->local_id} ({$riesgo->id})\n";
            }
        }
    }
}
echo "Done.\n";
