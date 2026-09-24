<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$urs = DB::table('unidades_responsables_gastos')->get();
$c = [];
foreach($urs as $ur) {
    if (!isset($c[$ur->ejercicio_id])) $c[$ur->ejercicio_id] = 0;
    $c[$ur->ejercicio_id]++;
    if ($ur->ejercicio_id == 19 && $c[$ur->ejercicio_id] <= 3) {
        echo "2027 UR: ID={$ur->unidad_responsable_gasto_id} - Nombre={$ur->nombre}\n";
    }
}
print_r($c);
