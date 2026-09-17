<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find all 2026 projects
$newProjects = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id', 'py.numero as py_num', 'ro.numero as ro_num', 'urg.numero as urg_num')
    ->where('ej.ejercicio', '2026')
    ->get();

$copied = 0;

foreach ($newProjects as $newPy) {
    // Check if this project already has activities
    $existing = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $newPy->proyecto_id)->count();
    if ($existing > 0) {
        continue;
    }

    // Find the corresponding 2025 project
    $oldPy = DB::connection('poa_prod')
        ->table('proyectos as py')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->select('py.proyecto_id')
        ->where('ej.ejercicio', '2025')
        ->where('urg.numero', $newPy->urg_num)
        ->where('ro.numero', $newPy->ro_num)
        ->where('py.numero', $newPy->py_num)
        ->first();

    if ($oldPy) {
        $oldActs = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $oldPy->proyecto_id)->get();
        foreach ($oldActs as $oa) {
            DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
                'proyecto_id' => $newPy->proyecto_id,
                'numero' => $oa->numero,
                'descripcion' => $oa->descripcion,
                'recursos_asociados' => $oa->recursos_asociados ?? null,
            ]);
            $copied++;
        }
    }
}

echo "Cloned $copied activities from 2025 to 2026.\n";
