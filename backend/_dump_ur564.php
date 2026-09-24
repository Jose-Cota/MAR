<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ur = Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 564)->first();
print_r($ur);
