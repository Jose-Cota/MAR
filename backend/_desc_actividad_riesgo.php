<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$desc = Illuminate\Support\Facades\DB::select('DESCRIBE actividad_riesgo');
print_r($desc);
