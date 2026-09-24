<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$urgEstructural = DB::connection('poa_prod')->table('unidades_responsables_gastos')
    ->where('ejercicio_id', 19) // 2026
    ->where('nombre', 'LIKE', '%Secretaría Administrativa%')
    ->where('unidad_responsable_gasto_id', '>=', 240)
    ->first();

if ($urgEstructural) {
    echo "URG Estructural: " . $urgEstructural->nombre . " (ID: " . $urgEstructural->unidad_responsable_gasto_id . ")\n";
    $ros = DB::connection('poa_prod')->table('responsables_operativos')
        ->where('unidad_responsable_gasto_id', $urgEstructural->unidad_responsable_gasto_id)
        ->get();
    foreach ($ros as $ro) {
        echo "- RO " . $ro->numero . ": " . $ro->nombre . " (ID: " . $ro->responsable_operativo_id . ")\n";
    }
}
