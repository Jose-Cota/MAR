<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $c = DB::connection('poa_prod')->select("
        SELECT COUNT(DISTINCT py.proyecto_id) as c
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE ej.ejercicio = '2025' AND urg.numero = '01'
    ");
    print_r($c);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
