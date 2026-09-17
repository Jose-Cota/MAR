<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$programas = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', 17)->get();
print_r($programas);
