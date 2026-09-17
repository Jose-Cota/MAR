<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicios = DB::connection('poa_prod')->table('ejercicios')->get();
foreach ($ejercicios as $e) {
    echo "Ejercicio: {$e->ejercicio}\n";
}
