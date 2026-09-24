<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mock = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);

$map_mock_ur_to_name = [];
foreach($mock['areas'] as $a) {
    $map_mock_ur_to_name[$a['id']] = $a['name'];
}

$urs = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 19)->get();
echo "URs found for 2027: " . $urs->count() . "\n";

$matched_urs = [];

function normalize($str) {
    return strtolower(trim(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','a','e','i','o','u'], $str)));
}

foreach($urs as $ur) {
    $name = normalize($ur->nombre);
    foreach($map_mock_ur_to_name as $id => $mock_name) {
        $mock_name_norm = normalize($mock_name);
        if ($name == $mock_name_norm || strpos($name, $mock_name_norm) !== false || strpos($mock_name_norm, $name) !== false) {
            $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
            break;
        }
        if ($id == 'PAAH' && strpos($name, 'armando ambriz') !== false) $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
        if ($id == 'PJHR' && strpos($name, 'jose jesus hernandez') !== false) $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
        if ($id == 'POVR' && strpos($name, 'osiris') !== false) $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
        if ($id == 'PKSL' && strpos($name, 'karina salgado') !== false) $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
        if ($id == 'PLPJC' && strpos($name, 'laura patricia jimenez') !== false) $matched_urs[$ur->unidad_responsable_gasto_id] = $id;
    }
}

echo "Matched URs: " . count($matched_urs) . "\n";
print_r($matched_urs);
