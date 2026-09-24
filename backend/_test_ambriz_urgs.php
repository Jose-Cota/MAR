<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 21)->first();
echo "URG 21 Name: " . $urg->nombre . "\n";

$urges2027 = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 20)->where('nombre', 'LIKE', '%Ambriz%')->get();
echo "2027 URGs with Ambriz:\n";
foreach ($urges2027 as $u) {
    echo "- ID: " . $u->unidad_responsable_gasto_id . " Name: " . $u->nombre . "\n";
}
