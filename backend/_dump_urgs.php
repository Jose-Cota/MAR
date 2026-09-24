<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->leftJoin('responsables_operativos as ro', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->leftJoin('proyectos as p', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('ej.ejercicio', 2027)
    ->select('urg.unidad_responsable_gasto_id', 'urg.nombre', 'urg.numero', 'p.proyecto_id')
    ->get();

foreach($urgs as $u) {
    echo "URG: {$u->unidad_responsable_gasto_id} | {$u->numero} | {$u->nombre} | Proy: {$u->proyecto_id}\n";
}
