<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bitacoras = DB::connection('poa_prod')->table('proyecto_bitacoras')->where('proyecto_id', 1421)->get();
print_r($bitacoras);
