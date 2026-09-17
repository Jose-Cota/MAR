<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ejercicio = '2026';
    
    // Check programadas
    $programadas = "
        SELECT COUNT(*) as c, SUM(mmp.numero) as total
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        JOIN metas m ON py.proyecto_id = m.proyecto_id
        JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
        WHERE ej.ejercicio = ?
    ";
    
    // Check alcanzadas
    $alcanzadas = "
        SELECT COUNT(*) as c, SUM(mma.numero) as total
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        JOIN metas m ON py.proyecto_id = m.proyecto_id
        JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
        WHERE ej.ejercicio = ?
    ";
    
    $resProg = DB::connection('poa_prod')->select($programadas, [$ejercicio])[0];
    $resAlc = DB::connection('poa_prod')->select($alcanzadas, [$ejercicio])[0];
    
    echo "==== AÑO 2026 EN PRODUCCION ====\n";
    echo "Registros PROGRAMADOS: {$resProg->c} (Suma total: {$resProg->total})\n";
    echo "Registros ALCANZADOS : {$resAlc->c} (Suma total: {$resAlc->total})\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
