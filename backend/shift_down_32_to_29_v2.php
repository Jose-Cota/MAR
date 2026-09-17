<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();
try {
    // 1. Shift the active ones down by 3
    $proyectos = DB::connection('poa_prod')->table('proyectos as py')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('ej.ejercicio', 2026)
        ->whereRaw('CAST(py.numero AS INTEGER) >= 32')
        ->select('py.proyecto_id', 'py.numero')
        ->get();

    $count = 0;
    foreach ($proyectos as $p) {
        $oldNum = (int)$p->numero;
        $newNum = $oldNum - 3;
        $paddedNum = str_pad($newNum, 2, '0', STR_PAD_LEFT);
        
        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $p->proyecto_id)
            ->update(['numero' => $paddedNum]);
            
        $count++;
    }

    // 2. To avoid duplicates, set the BAJA ones to a high number or NULL?
    // The user said they "eliminated" them, so they shouldn't conflict with the new 29,30,31.
    // Let's change their number to '99' to avoid duplicate numbering in the UI.
    $bajas = DB::connection('poa_prod')->table('proyectos as py')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('ej.ejercicio', 2026)
        ->whereIn('py.proyecto_id', [892, 895, 907]) // These are the known BAJA projects that were 29, 30, 31
        ->update(['py.numero' => '99']);

    DB::connection('poa_prod')->commit();
    echo "Successfully shifted down $count projects from >= 32 by subtracting 3.\n";
    echo "Updated $bajas BAJA projects to '99' to prevent numbering collision.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
