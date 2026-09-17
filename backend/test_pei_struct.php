<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$linea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->first();
$obj = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->first();

echo "Linea:\n";
print_r((array)$linea);
echo "\nObjetivo:\n";
print_r((array)$obj);
