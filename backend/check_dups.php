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
    ->whereIn('py.numero', ['29', '30', '31'])
    ->select('py.proyecto_id', 'py.numero', 'py.estatus', 'py.nombre')
    ->get();

foreach ($proyectos as $p) {
    echo "{$p->proyecto_id} | NUM: {$p->numero} | ESTATUS: {$p->estatus} | {$p->nombre}\n";
}
