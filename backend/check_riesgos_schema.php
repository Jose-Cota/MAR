<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$ejercicios = DB::table('ejercicios')->get();
print_r($ejercicios);

$control = DB::table('riesgo_controles')->first();
print_r($control);
