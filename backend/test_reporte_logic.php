<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $mesId = 6;
    $ejercicio = '2025';
    
    $query = "
        SELECT 
            urg.numero as urg,
            CONCAT(urg.numero, '-', py.numero) as py,
            SUM(
                COALESCE((
                    SELECT SUM(mmp.numero)
                    FROM metas m
                    JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
                    WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mmp.mes_id <= ?
                ), 0)
            ) as total_programado,
            SUM(
                COALESCE((
                    SELECT SUM(mma.numero)
                    FROM metas m
                    JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
                    WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mma.mes_id <= ?
                ), 0)
            ) as total_alcanzado
        FROM proyectos as py
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios as ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE ej.ejercicio = ?
        GROUP BY urg.numero, py.numero
        ORDER BY urg.numero, py.numero
        LIMIT 5
    ";
    
    $proyectos = DB::connection('poa_prod')->select($query, [$mesId, $mesId, $ejercicio]);
    
    foreach ($proyectos as $p) {
        $label = $p->urg . '-' . $p->py;
        
        $avance = 0;
        if ($p->total_programado > 0) {
            $avance = ($p->total_alcanzado / $p->total_programado); 
        } else {
            $avance = ($p->total_alcanzado > 0) ? 100 : 0; 
        }
        
        echo "LABEL: {$p->py} | PROG: {$p->total_programado} | ALC: {$p->total_alcanzado} | AVANCE FINAL: {$avance}\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
