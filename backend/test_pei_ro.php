<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectosConPei = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('pei_proyecto_alineaciones as ppa', 'py.proyecto_id', '=', 'ppa.proyecto_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->join('ejercicios as ej', 'pg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('ro.numero', '07') // Filter by RO 07
    ->select(
        'py.proyecto_id',
        'urg.numero as urg',
        'ro.numero as ro',
        'pg.numero as pg',
        'sp.numero as sp',
        'py.numero as py_num',
        'py.nombre',
        'ej.ejercicio',
        'ppa.pei_linea_estrategica_id',
        'ppa.pei_objetivo_estrategico_id'
    )
    ->get();

if ($proyectosConPei->isEmpty()) {
    echo "No projects for RO 07 have PEI alignment.\n";
    // Let's just find ANY project that has PEI
    $any = DB::connection('poa_prod')
        ->table('proyectos as py')
        ->join('pei_proyecto_alineaciones as ppa', 'py.proyecto_id', '=', 'ppa.proyecto_id')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
        ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
        ->select(
            'urg.numero as urg',
            'ro.numero as ro',
            'pg.numero as pg',
            'sp.numero as sp',
            'py.numero as py_num',
            'py.nombre'
        )
        ->take(5)
        ->get();
    echo "Here are 5 random projects that DO have PEI:\n";
    foreach($any as $a) {
        echo "- {$a->urg}.{$a->ro}.{$a->pg}.{$a->sp}.{$a->py_num} : {$a->nombre}\n";
    }
} else {
    foreach($proyectosConPei as $p) {
        echo "Proyecto (Ejercicio {$p->ejercicio}): {$p->urg}.{$p->ro}.{$p->pg}.{$p->sp}.{$p->py_num} - {$p->nombre}\n";
    }
}
