<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $res = Illuminate\Support\Facades\DB::select("
        SELECT 
            urg.numero as urg,
            urg.nombre as urg_nombre,
            py.numero as py,
            py.nombre as py_nombre,
            SUM(
                COALESCE((
                    SELECT SUM(mmp.numero)
                    FROM metas m
                    JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
                    WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mmp.mes_id <= 6
                ), 0)
            ) as total_programado
        FROM proyectos as py
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN subprogramas sp ON py.subprograma_id = sp.subprograma_id
        JOIN programas pg ON sp.programa_id = pg.programa_id
        JOIN ejercicios as ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE ej.ejercicio = 2026 AND urg.numero = '01'
        GROUP BY urg.numero, urg.nombre, py.numero, py.nombre
        ORDER BY urg.numero, py.numero
    ");
    foreach ($res as $r) {
        echo "PY: {$r->py}\n";
        echo "Nombre: [" . $r->py_nombre . "]\n";
        echo "Length: " . strlen($r->py_nombre) . "\n";
        echo "---\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
