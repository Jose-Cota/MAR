<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Restore the ones that were changed
DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1425)->update(['status' => 'Validacion']);
DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1464)->update(['status' => 'Cerrada']);

echo "Restored IDs 1425 and 1464 to their original statuses.\n";
