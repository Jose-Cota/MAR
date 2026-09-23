<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$r = DB::table('riesgos')->where('ejercicio_id', 17)->where('local_id', 'R1')->where('area_id', 11)->first();
echo "Factores: " . $r->factores . "\n";
echo "Factores internos: " . $r->factores_internos . "\n";
