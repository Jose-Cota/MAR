<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ejercicio = '2025';
    
    // Check if there are ANY metas alcanzadas for 2026 ANY month
    $query = "
        SELECT 
            py.numero as py_numero,
            urg.numero as urg_numero,
            mma.mes_id,
            mma.numero as alcanzado
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        JOIN metas m ON py.proyecto_id = m.proyecto_id
        JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
        WHERE ej.ejercicio = ? AND mma.numero > 0
        LIMIT 10
    ";
    
    $results = DB::connection('poa_prod')->select($query, [$ejercicio]);
    echo "Registros con avance real > 0 en 2026 (Produccion): " . count($results) . "\n";
    print_r($results);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
