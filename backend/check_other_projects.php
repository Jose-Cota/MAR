<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $projectIds = [1421, 1423, 1425, 1464, 1466];
    
    $pys = DB::connection('poa_prod')->table('proyectos')
        ->join('responsables_operativos as ro', 'proyectos.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('subprogramas as sp', 'proyectos.subprograma_id', '=', 'sp.subprograma_id')
        ->join('programas as p', 'sp.programa_id', '=', 'p.programa_id')
        ->whereIn('proyectos.proyecto_id', $projectIds)
        ->select(
            'proyectos.proyecto_id', 
            'proyectos.nombre', 
            'p.numero as pg_num', 
            'sp.numero as sp_num', 
            'urg.numero as urg_num', 
            'ro.numero as ro_num', 
            'proyectos.numero as py_num',
            'proyectos.nombre_responsable_operativo',
            'proyectos.cargo_responsable_operativo',
            'proyectos.nombre_titular',
            'proyectos.responsable_ficha'
        )
        ->orderBy('ro.numero')
        ->get();
        
    echo "=== ESTADO DE METAS Y ACTIVIDADES EN 2027 ===\n";
    foreach($pys as $p) {
        $clave = $p->pg_num . '.' . $p->sp_num . '.' . $p->urg_num . '.' . $p->ro_num . '.' . $p->py_num;
        $metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $p->proyecto_id)->count();
        $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $p->proyecto_id)->count();
        $act_new = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->count();
        $act_old = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->count();
        
        echo "- Clave: $clave (ID: {$p->proyecto_id})\n";
        echo "  Responsable: {$p->nombre_responsable_operativo} ({$p->cargo_responsable_operativo})\n";
        echo "  Titular: {$p->nombre_titular}\n";
        echo "  Responsable Ficha: {$p->responsable_ficha}\n";
        echo "  Metas: $metas | Indicadores: $inds | Actividades_Sustantivas (2027): $act_new | Acciones_Sustantivas (2026): $act_old\n\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
