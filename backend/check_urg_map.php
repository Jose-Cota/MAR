<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check what URG ID 564 is
$urg564 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 564)->first();
echo "URG 564: " . json_encode($urg564) . "\n\n";

// Check if URG 20 in ej 19 matches by numero with URG 564
$urg20 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 20)->first();
echo "URG 20: " . json_encode($urg20) . "\n\n";

// Find the URG in ejercicio 19 that has the same numero as URG 564
if ($urg564) {
    $match = DB::table('unidades_responsables_gastos')
        ->where('numero', $urg564->numero)
        ->where('ejercicio_id', 19)
        ->first();
    echo "URG with numero {$urg564->numero} in ejercicio 19: " . json_encode($match) . "\n";
}
