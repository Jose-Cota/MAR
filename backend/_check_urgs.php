<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')
    ->where('ejercicio_id', 19)
    ->select('nombre', 'unidad_responsable_gasto_id')
    ->get();
    
foreach ($urgs as $u) {
    echo "{$u->unidad_responsable_gasto_id} | {$u->nombre}\n";
}
