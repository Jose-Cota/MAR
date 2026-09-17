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
    ->where('ej.ejercicio', 2026)
    ->select('py.proyecto_id', 'py.numero', 'py.nombre as denominacion', 'urg.numero as urg_num', 'ro.numero as ro_num', 'py.pg', 'py.sp')
    ->orderBy('urg.numero')
    ->orderBy('ro.numero')
    ->orderBy('py.pg')
    ->orderBy('py.sp')
    ->orderBy('py.numero')
    ->get();

echo "ID\tURG\tRO\tPG\tSP\tNUM\tDENOMINACION\n";
foreach ($proyectos as $p) {
    echo "{$p->proyecto_id}\t{$p->urg_num}\t{$p->ro_num}\t{$p->pg}\t{$p->sp}\t{$p->numero}\t".substr($p->denominacion, 0, 30)."\n";
}
