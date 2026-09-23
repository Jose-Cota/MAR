<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\extracted_seed.json'), true);

$urgMap = [];
foreach ($seed['areas'] as $area) {
    $urg = DB::table('unidades_responsables_gastos')->where('nombre', $area['name'])->first();
    if ($urg) {
        $urgMap[$area['id']] = $urg->unidad_responsable_gasto_id;
    }
}

$roMap = []; // urg_id -> first ro_id
foreach ($urgMap as $aid => $urgId) {
    $ro = DB::table('responsables_operativos')->where('unidad_responsable_gasto_id', $urgId)->first();
    if ($ro) {
        $roMap[$urgId] = $ro->responsable_operativo_id;
    }
}

echo "URG mapped: " . count($urgMap) . "\n";
echo "RO mapped: " . count($roMap) . "\n";
