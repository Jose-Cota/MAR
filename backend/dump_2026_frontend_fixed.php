<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->join('subprograma_pei_alineaciones as spal', 'py.alineacion_id', '=', 'spal.alineacion_id')
    ->join('subprogramas as sp', 'spal.subprograma_id', '=', 'sp.subprograma_id')
    ->join('pei_programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->where('ej.ejercicio', 2026)
    ->orderBy('urg.numero', 'asc')
    ->orderBy('ro.numero', 'asc')
    ->orderByRaw('CAST(py.numero AS INTEGER) asc')
    ->select('py.proyecto_id', 'urg.numero as urg_num', 'ro.numero as ro_num', 'pg.numero as pg_num', 'sp.numero as sp_num', 'py.numero as py_num', 'py.nombre as denominacion', 'py.estatus')
    ->get();

echo "ID\tURG\tRO\tPG\tSP\tPY\tESTATUS\tDENOMINACION\n";
foreach ($proyectos as $p) {
    echo "{$p->proyecto_id}\t{$p->urg_num}\t{$p->ro_num}\t{$p->pg_num}\t{$p->sp_num}\t{$p->py_num}\t{$p->estatus}\t{$p->denominacion}\n";
}
