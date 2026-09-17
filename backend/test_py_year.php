<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->select('py.proyecto_id', 'ej.ejercicio', 'py.nombre', 'sp.numero as sp_num', 'py.numero as py_num')
    ->where('sp.numero', '02')
    ->where('py.numero', '05')
    ->get();

foreach ($py as $p) {
    echo "ID: {$p->proyecto_id} | Year: {$p->ejercicio} | py_num: {$p->py_num} | name: {$p->nombre}\n";
    $align = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $p->proyecto_id)->first();
    echo "Alignment: " . ($align ? json_encode($align) : "NULL") . "\n";
}
