<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$projects = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->leftJoin('pei_proyecto_alineaciones as ppa', 'py.proyecto_id', '=', 'ppa.proyecto_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->select('py.proyecto_id', 'py.nombre', 'sp.numero as sp_numero', 'ej.ejercicio')
    ->whereIn('ej.ejercicio', ['2026', '2027'])
    ->whereNull('ppa.proyecto_id')
    ->get();

foreach ($projects as $py) {
    echo "Missing PEI: Proy {$py->proyecto_id} | SP: {$py->sp_numero} | Name: {$py->nombre} | Year: {$py->ejercicio}\n";
}
