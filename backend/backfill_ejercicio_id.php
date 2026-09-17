<?php

// 1. Backfill subprogramas
$subprogramas = DB::connection('poa_prod')->table('subprogramas as sp')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->select('sp.subprograma_id', 'pg.ejercicio_id')
    ->get();

$countSp = 0;
foreach($subprogramas as $sp) {
    if ($sp->ejercicio_id) {
        DB::connection('poa_prod')->table('subprogramas')
            ->where('subprograma_id', $sp->subprograma_id)
            ->update(['ejercicio_id' => $sp->ejercicio_id]);
        $countSp++;
    }
}
echo "Subprogramas actualizados: {$countSp}\n";

// 2. Backfill responsables_operativos
$ros = DB::connection('poa_prod')->table('responsables_operativos as ro')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('ro.responsable_operativo_id', 'urg.ejercicio_id')
    ->get();

$countRo = 0;
foreach($ros as $ro) {
    if ($ro->ejercicio_id) {
        DB::connection('poa_prod')->table('responsables_operativos')
            ->where('responsable_operativo_id', $ro->responsable_operativo_id)
            ->update(['ejercicio_id' => $ro->ejercicio_id]);
        $countRo++;
    }
}
echo "Responsables Operativos actualizados: {$countRo}\n";

// 3. Backfill proyectos
$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('py.proyecto_id', 'urg.ejercicio_id')
    ->get();

$countPy = 0;
foreach($proyectos as $py) {
    if ($py->ejercicio_id) {
        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $py->proyecto_id)
            ->update(['ejercicio_id' => $py->ejercicio_id]);
        $countPy++;
    }
}
echo "Proyectos actualizados: {$countPy}\n";
