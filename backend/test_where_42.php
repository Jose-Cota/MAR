<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $res = DB::connection('poa_prod')->select("
        SELECT urg.numero as urg, ro.numero as ro, pg.numero as pg, sp.numero as sp, py.numero as py
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN subprogramas sp ON py.subprograma_id = sp.subprograma_id
        JOIN programas pg ON sp.programa_id = pg.programa_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        WHERE py.numero IN ('41', '42')
    ");
    print_r($res);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
