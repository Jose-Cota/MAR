<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "--- DESGLOSE POR SUB-ÁREA (Responsable Operativo) EN 2027 ---\n";
$por_ro_2027 = DB::connection('poa_prod')->select("
    SELECT ro.nombre as ro_nombre, COUNT(a.id) as total
    FROM actividades_sustantivas a
    JOIN proyectos p ON a.proyecto_id = p.proyecto_id
    JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id
    WHERE p.ejercicio_id = 19
    GROUP BY ro.nombre
    ORDER BY total DESC
");

foreach ($por_ro_2027 as $row) {
    echo "{$row->ro_nombre}: {$row->total} actividades\n";
}
