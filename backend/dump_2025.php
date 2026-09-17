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
    ->where('ej.ejercicio', 2025)
    ->orderBy('urg.numero', 'asc')
    ->orderBy('ro.numero', 'asc')
    ->orderByRaw('CAST(py.numero AS INTEGER) asc')
    ->select('py.proyecto_id', 'urg.numero as urg_num', 'ro.numero as ro_num', 'py.numero', 'py.nombre as denominacion')
    ->get();

echo "ID\tURG\tRO\tNUM\tDENOMINACION\n";
foreach ($proyectos as $p) {
    echo "{$p->proyecto_id}\t{$p->urg_num}\t{$p->ro_num}\t{$p->numero}\t{$p->denominacion}\n";
}
