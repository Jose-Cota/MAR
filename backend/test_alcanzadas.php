<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ejercicio = '2026';
    
    // Check if there are ANY metas alcanzadas for 2026
    $query = "
        SELECT 
            py.proyecto_id, urg.numero as urg, py.numero as py,
            SUM(mma.numero) as total_alcanzado
        FROM proyectos as py
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios as ej ON urg.ejercicio_id = ej.ejercicio_id
        JOIN metas m ON py.proyecto_id = m.proyecto_id
        JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
        WHERE ej.ejercicio = ?
        GROUP BY py.proyecto_id, urg.numero, py.numero
        HAVING total_alcanzado > 0
    ";
    
    $results = DB::connection('poa_prod')->select($query, [$ejercicio]);
    echo "Proyectos con avance real en 2026 (Produccion): " . count($results) . "\n";
    if (count($results) > 0) {
        print_r($results[0]);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
