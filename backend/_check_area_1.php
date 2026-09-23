<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ros = DB::table('riesgos')->where('ejercicio_id', 19)->where('area_id', 1)->count();
print_r($ros);
