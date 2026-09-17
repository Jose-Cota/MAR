<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$p1438 = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->select('py.*', 'ro.numero as ro_num')
    ->where('py.proyecto_id', 1438)
    ->first();

echo "1438: num=" . $p1438->numero . " subprograma_id=" . $p1438->subprograma_id . " RO_num=" . $p1438->ro_num . "\n";

// Find 2025 equivalent
$oldPy = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id')
    ->where('ej.ejercicio', '2025')
    ->where('ro.numero', $p1438->ro_num)
    ->where('py.numero', $p1438->numero)
    ->first();

if ($oldPy) {
    echo "Old Py is: " . $oldPy->proyecto_id . "\n";
    $acts = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $oldPy->proyecto_id)->count();
    echo "Old Py Acts: " . $acts . "\n";
} else {
    echo "Old Py not found!\n";
}
