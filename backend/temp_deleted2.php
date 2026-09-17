<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$deleted = DB::connection('poa_prod')->select('
    SELECT bak.proyecto_id, bak.numero, bak.nombre 
    FROM proyectos_backup_21082026 bak 
    LEFT JOIN proyectos py ON bak.proyecto_id = py.proyecto_id 
    WHERE py.proyecto_id IS NULL AND bak.proyecto_id > 800
');
print_r($deleted);
