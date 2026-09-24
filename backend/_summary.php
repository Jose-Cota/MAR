<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "--- CANTIDAD DE ACTIVIDADES POR AÑO ---\n";
$por_ano = DB::connection('poa_prod')->select("
    SELECT e.ejercicio, COUNT(a.id) as total
    FROM actividades_sustantivas a
    JOIN proyectos p ON a.proyecto_id = p.proyecto_id
    JOIN ejercicios e ON p.ejercicio_id = e.ejercicio_id
    GROUP BY e.ejercicio
    ORDER BY e.ejercicio DESC
");

foreach ($por_ano as $row) {
    echo "Año {$row->ejercicio}: {$row->total} actividades\n";
}

echo "\n--- CANTIDAD DE ACTIVIDADES POR UR EN 2027 ---\n";
$por_ur_2027 = DB::connection('poa_prod')->select("
    SELECT urg.nombre as ur_nombre, COUNT(a.id) as total
    FROM actividades_sustantivas a
    JOIN proyectos p ON a.proyecto_id = p.proyecto_id
    JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
    WHERE p.ejercicio_id = 19
    GROUP BY urg.nombre
    ORDER BY total DESC
");

foreach ($por_ur_2027 as $row) {
    echo "{$row->ur_nombre}: {$row->total} actividades\n";
}
