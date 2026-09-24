<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$ccla = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('nombre', 'LIKE', '%Controversias%')->first();
echo "CCLA URG ID: " . $ccla->unidad_responsable_gasto_id . "\n";
