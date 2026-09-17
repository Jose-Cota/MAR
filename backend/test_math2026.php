<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $data = DB::connection('poa_prod')->select("
        SELECT 
            urg.numero as urg,
            py.numero as py,
            (SELECT SUM(mmp.numero) FROM metas m JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mmp.mes_id <= 7) as prog,
            (SELECT SUM(mma.numero) FROM metas m JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mma.mes_id <= 7) as alc
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE ej.ejercicio = '2026'
        LIMIT 5
    ");
    print_r($data);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
