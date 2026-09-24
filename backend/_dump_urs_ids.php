<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$urs = DB::table('unidades_responsables_gastos')->get();
foreach($urs as $ur) {
    if ($ur->ejercicio_id == 19) {
        echo "ID={$ur->unidad_responsable_gasto_id} - Nombre={$ur->nombre}\n";
    }
}
