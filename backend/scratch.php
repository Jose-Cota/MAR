<?php

$proyecto = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('py.proyecto_id', 'py.numero as proyecto_numero', 'urg.numero as urg_numero')
    ->where('py.numero', '40')
    ->where('urg.numero', '05')
    ->first();

echo "Proyecto:\n";
print_r($proyecto);

echo "\nObjetivos:\n";
print_r(DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->get()->toArray());

echo "\nLineas:\n";
print_r(DB::connection('poa_prod')->table('pei_lineas_estrategicas')->get()->toArray());

