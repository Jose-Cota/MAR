<?php
$proyecto = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as e', 'py.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('py.proyecto_id', 'e.ejercicio')
    ->where('py.numero', '40')
    ->where('urg.numero', '05')
    ->where('e.ejercicio', 2027)
    ->first();

echo "Proyecto ID: " . $proyecto->proyecto_id . "\n";

$programa = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio', 2027)->first();
echo "Programa ID: " . $programa->pei_programa_id . "\n";

// Linea Estrategica (Objetivo Estrategico 3)
$le = DB::connection('poa_prod')->table('pei_lineas_estrategicas')
    ->where('pei_programa_id', $programa->pei_programa_id)
    ->where('numero', 3)
    ->first();
echo "PEI Linea Estrategica ID (Obj Est 3): " . $le->pei_linea_estrategica_id . "\n";

// Objetivos Estrategicos (Lineas I y V)
$oe_I = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
    ->where('pei_linea_estrategica_id', $le->pei_linea_estrategica_id)
    ->where('numero', 1)
    ->first();
$oe_V = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
    ->where('pei_linea_estrategica_id', $le->pei_linea_estrategica_id)
    ->where('numero', 5)
    ->first();

echo "PEI Objetivo Estrategico ID (Linea I): " . $oe_I->pei_objetivo_estrategico_id . "\n";
echo "PEI Objetivo Estrategico ID (Linea V): " . $oe_V->pei_objetivo_estrategico_id . "\n";

