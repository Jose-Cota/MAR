<?php
use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')
    ->leftJoin('responsables_operativos as ro', 'proyectos.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->leftJoin('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('proyectos.proyecto_id', 'proyectos.numero as py', 'ro.numero as ro', 'urg.numero as urg', 'proyectos.nombre')
    ->where('proyectos.ejercicio_id', 19)
    ->get();

$incompletos = [];

foreach ($proyectos as $p) {
    // Check if it has Meta Principal
    $principales = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'principal')->count();
    
    // Check if it has Meta Complementaria
    $comp = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'complementaria')->get();
    
    // Check if any indicator is attached to a Complementaria
    $has_indicator_on_comp = false;
    foreach ($comp as $c) {
        $ind_count = DB::table('indicadores')->where('meta_id', $c->meta_id)->count();
        if ($ind_count > 0) {
            $has_indicator_on_comp = true;
            break;
        }
    }
    
    if ($principales == 0 || count($comp) == 0 || !$has_indicator_on_comp) {
        $incompletos[] = [
            'id' => $p->proyecto_id,
            'clave' => $p->urg . '.' . $p->ro . '... .' . $p->py,
            'nombre' => $p->nombre,
            'tiene_principal' => $principales > 0 ? 'Si' : 'No',
            'tiene_complementaria' => count($comp) > 0 ? 'Si' : 'No',
            'tiene_indicador_en_comp' => $has_indicator_on_comp ? 'Si' : 'No'
        ];
    }
}

echo "=== RESULTADOS ===\n";
echo json_encode($incompletos, JSON_PRETTY_PRINT);
exit;
