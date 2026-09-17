<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Look for '1047' in other databases or something, wait I don't have access to other DBs maybe.
// Let's just find an existing one with id 1047 if it exists in any other table? No.
// Let's check what ID 1047 could be by finding the maximum ID currently.
$maxId = DB::connection('poa_prod')->table('unidades_medidas')->max('unidad_medida_id');
echo "Max ID: $maxId\n";

// Find all units for 2027
$units = DB::connection('poa_prod')->table('unidades_medidas')->where('ejercicio_id', 3)->get(); // assuming 3 is 2027
print_r($units);
