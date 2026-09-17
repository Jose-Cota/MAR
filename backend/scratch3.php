<?php
$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as e', 'py.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('py.proyecto_id', 'py.numero as num_proy', 'urg.numero as num_urg', 'e.ejercicio')
    ->where('py.numero', '40')
    ->where('urg.numero', '05')
    ->get();

echo "Proyectos 40 en UR 05:\n";
foreach ($proyectos as $p) {
    echo "ID: {$p->proyecto_id} | Ejercicio: {$p->ejercicio} | UR: {$p->num_urg} | Proyecto: {$p->num_proy}\n";
}
