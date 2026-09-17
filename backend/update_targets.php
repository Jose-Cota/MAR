<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyecto_id = 1433;
DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', $proyecto_id)->update(['status' => 'Captura', 'fecha_verificacion' => null]);
echo "Updated to Captura successfully.\n";
