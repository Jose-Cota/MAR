<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$lineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->whereIn('pei_linea_estrategica_id', [7])->get();
print_r($lineas);

$objs = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_objetivo_estrategico_id', [26])->get();
print_r($objs);
