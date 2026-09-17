<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$acts2026 = DB::connection('poa_prod')
    ->table('acciones_sustantivas as a')
    ->join('proyectos as py', 'a.proyecto_id', '=', 'py.proyecto_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('ej.ejercicio', '2026')
    ->count();

echo "Acts 2026: $acts2026\n";

// I'll just delete them and re-clone correctly if there was a partial fail.
if ($acts2026 > 0) {
    DB::connection('poa_prod')
        ->table('acciones_sustantivas')
        ->whereIn('proyecto_id', function($query) {
            $query->select('py.proyecto_id')
                ->from('proyectos as py')
                ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
                ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
                ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
                ->where('ej.ejercicio', '2026');
        })->delete();
    echo "Deleted all 2026 acts.\n";
}
