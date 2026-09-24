<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('nombre', 'like', '%Materiales%')->first();
echo "URG ID: " . $urg->unidad_responsable_gasto_id . "\n";
