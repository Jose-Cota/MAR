<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e = (array)DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2026)->first();
unset($e['ejercicio_id']);
$e['ejercicio'] = 2027;
DB::connection('poa_prod')->table('ejercicios')->insert($e);
echo "Created 2027\n";
