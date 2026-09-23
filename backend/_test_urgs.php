<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$arr = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 1)->pluck('nombre')->toArray();
print_r($arr);
