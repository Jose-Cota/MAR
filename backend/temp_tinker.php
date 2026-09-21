<?php
$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 19)
    ->select('proyectos.proyecto_id as id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id')
    ->get();
echo json_encode($proyectos);
