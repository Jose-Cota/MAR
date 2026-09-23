<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urgs = DB::table('unidades_responsables_gastos')->where('nombre', 'Secretaría Administrativa')->get();
foreach ($urgs as $u) {
    echo "URG: {$u->unidad_responsable_gasto_id} | Ej: {$u->ejercicio_id}\n";
}
