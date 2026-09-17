<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->select('
    SELECT proyecto_id, numero, nombre, updated_at 
    FROM proyectos 
    WHERE updated_at > "2026-08-20"
');
print_r($proyectos);
