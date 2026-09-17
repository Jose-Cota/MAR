<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$um = DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', 1047)->first();
print_r($um);
