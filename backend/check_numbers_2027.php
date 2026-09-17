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
    ->where('ej.ejercicio', 2027)
    ->whereRaw('CAST(py.numero AS INTEGER) >= 29')
    ->select('py.proyecto_id', 'py.numero', 'py.nombre')
    ->orderByRaw('CAST(py.numero AS INTEGER) ASC')
    ->get();

foreach ($proyectos as $p) {
    echo "ID: {$p->proyecto_id} | NUM: {$p->numero} | {$p->nombre}\n";
}
