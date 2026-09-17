<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();
try {
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

    DB::connection('poa_prod')->commit();
    echo "Successfully shifted down $count projects from >= 32 by subtracting 3.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
