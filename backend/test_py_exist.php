<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py = DB::connection('poa_prod')->table('proyectos')->whereIn('proyecto_id', [1068, 1069, 1070])->get();
echo "Proyectos in DB:\n" . json_encode($py) . "\n";
