<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urgs = DB::table('unidades_responsables_gastos')->whereIn('unidad_responsable_gasto_id', range(1,25))->get();
foreach($urgs as $u) {
    echo $u->unidad_responsable_gasto_id . ' - ' . $u->nombre . "\n";
}
