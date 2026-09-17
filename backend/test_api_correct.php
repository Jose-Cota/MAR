<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ejercicio = '2026';
    $mesId = 6;
    
    $query = "
        SELECT 
            urg.numero as urg,
            py.numero as py,
            COALESCE((
                SELECT SUM(mmp.numero)
                FROM metas m
                JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
                WHERE m.proyecto_id = py.proyecto_id AND mmp.mes_id <= ?
            ), 0) as total_programado,
            COALESCE((
                SELECT SUM(mma.numero)
                FROM metas m
                JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
                WHERE m.proyecto_id = py.proyecto_id AND mma.mes_id <= ?
            ), 0) as total_alcanzado
        FROM proyectos as py
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios as ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE ej.ejercicio = ?
        ORDER BY urg.numero, py.numero
    ";
    
    $start = microtime(true);
    $results = DB::connection('poa_prod')->select($query, [$mesId, $mesId, $ejercicio]);
    echo "Results: " . count($results) . " in " . (microtime(true) - $start) . " seconds\n";
    if (count($results) > 0) {
        print_r($results[0]);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
