<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $res = DB::connection('poa_prod')->select("
        SELECT urg.numero as urg, py.numero as py, py.nombre
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE py.numero IN ('41', '42') OR py.numero > 40
        ORDER BY urg.numero, py.numero
        LIMIT 10
    ");
    print_r($res);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
