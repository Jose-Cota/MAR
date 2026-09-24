<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$query = DB::connection('poa_prod')
    ->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as e', 'urg.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('urg.*', 'e.ejercicio')
    ->where('e.ejercicio', 2027)
    ->where('urg.unidad_responsable_gasto_id', '<', 240)
    ->orderBy('urg.numero')
    ->get();

echo "URGs 2027: " . count($query) . "\n";
