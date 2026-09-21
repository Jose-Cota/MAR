<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check poa_prod URGs for ejercicio 19
$urgs_poa = DB::connection('poa_prod')
    ->table('unidades_responsables_gastos')
    ->where('ejercicio_id', 19)
    ->get();

echo "URGs en poa_prod para ej_id=19:\n";
foreach ($urgs_poa as $u) {
    echo "  id={$u->unidad_responsable_gasto_id}, num={$u->numero}, nombre={$u->nombre}\n";
}

echo "\nTotal: " . count($urgs_poa) . "\n";
echo "Max ID: " . $urgs_poa->max('unidad_responsable_gasto_id') . "\n\n";

// Now check if urg_id 564 exists in poa_prod
$urg564 = DB::connection('poa_prod')
    ->table('unidades_responsables_gastos')
    ->where('unidad_responsable_gasto_id', 564)
    ->first();
echo "URG 564 en poa_prod: " . json_encode($urg564) . "\n";
