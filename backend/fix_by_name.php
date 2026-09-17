<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$yearsToFix = ['2026', '2027'];
$totalCopied = 0;

foreach ($yearsToFix as $year) {
    $newProjects = DB::connection('poa_prod')
        ->table('proyectos as py')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->select('py.proyecto_id', 'py.nombre')
        ->where('ej.ejercicio', $year)
        ->get();

    $copiedForYear = 0;

    foreach ($newProjects as $newPy) {
        $existing = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $newPy->proyecto_id)->count();
        if ($existing > 0) continue;

        // Find 2025 equivalent by exact name match
        $oldPy = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select('py.proyecto_id')
            ->where('ej.ejercicio', '2025')
            ->where('py.nombre', $newPy->nombre)
            ->first();

        if ($oldPy) {
            $oldActs = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $oldPy->proyecto_id)->get();
            foreach ($oldActs as $oa) {
                DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
                    'proyecto_id' => $newPy->proyecto_id,
                    'numero' => $oa->numero,
                    'descripcion' => $oa->descripcion,
                ]);
                $copiedForYear++;
                $totalCopied++;
            }
        }
    }
    echo "Cloned $copiedForYear activities for $year.\n";
}

echo "Total cloned: $totalCopied\n";
