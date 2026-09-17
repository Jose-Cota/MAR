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
    ->orderBy('urg.numero')
    ->orderBy('ro.numero')
    ->orderBy('py.proyecto_id')
    ->select('py.proyecto_id', 'py.numero as py_numero', 'urg.numero as urg_numero', 'py.nombre')
    ->get();

$i = 1;
foreach ($proyectos as $p) {
    echo "Seq: ".str_pad($i, 2, '0', STR_PAD_LEFT)." | ID: {$p->proyecto_id} | URG: {$p->urg_numero} | Current PY: {$p->py_numero} | {$p->nombre}\n";
    $i++;
}
