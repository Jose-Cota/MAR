<?php
$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 19)
    ->select('proyectos.proyecto_id as id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id')
    ->get();

$results = [];
foreach ($proyectos as $p) {
    $count = DB::table('actividades_sustantivas')->where('proyecto_id', $p->id)->count();
    if (!isset($results[$p->urg_id])) {
        $results[$p->urg_id] = 0;
    }
    $results[$p->urg_id] += $count;
}

echo json_encode($results);
