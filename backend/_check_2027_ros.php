<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ros = DB::table('proyectos')->where('ejercicio_id', 18)->pluck('responsable_operativo_id')->unique()->toArray();
print_r($ros);
