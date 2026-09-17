<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->join('ejercicios as ej', 'pg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('py.numero', '10')
    ->where('urg.numero', '04')
    ->where('ej.ejercicio', '2026')
    ->select('py.proyecto_id', 'urg.numero as urg', 'ro.numero as ro', 'py.numero as py', 'py.status', 'py.nombre')
    ->get();

foreach ($proyectos as $p) {
    echo "ID: {$p->proyecto_id} | URG: {$p->urg} | RO: {$p->ro} | PY: {$p->py} | Status: {$p->status} | {$p->nombre}\n";
}
