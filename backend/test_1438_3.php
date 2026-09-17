<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1438)->first();
echo json_encode($py) . "\n";
