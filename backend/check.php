<?php
$proyectoId = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('urg.unidad_responsable_gasto_id', 'py.proyecto_id')
    ->where('py.numero', '01')
    ->first(); // 01.01.01.01.01 is basically just matching the UR 01

if (!$proyectoId) {
    echo 'Proyecto no encontrado';
} else {
    $validadores = \App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'Validador');
    })
    ->whereHas('unidadesResponsables', function($q) use ($proyectoId) {
        $q->where('unidades_responsables_gastos.unidad_responsable_gasto_id', $proyectoId->unidad_responsable_gasto_id);
    })
    ->get();
    
    if ($validadores->isEmpty()) {
        echo 'No hay NINGUN validador asignado a la URG de este proyecto';
    } else {
        foreach($validadores as $v) {
            echo $v->nombre . ' ' . $v->apellido_paterno . ' - Correo: ' . ($v->correo ?? 'SIN CORREO') . "\n";
        }
    }
}
