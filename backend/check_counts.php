<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$c_actividades = DB::table('actividades_sustantivas')->count();
$c_acciones = DB::table('acciones_sustantivas')->count();

echo "Default DB:\n";
echo "Count in actividades_sustantivas: $c_actividades\n";
echo "Count in acciones_sustantivas: $c_acciones\n";

$c_actividades_prod = DB::connection('poa_prod')->table('actividades_sustantivas')->count();
$c_acciones_prod = DB::connection('poa_prod')->table('acciones_sustantivas')->count();

echo "poa_prod DB:\n";
echo "Count in actividades_sustantivas: $c_actividades_prod\n";
echo "Count in acciones_sustantivas: $c_acciones_prod\n";
