<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$areaId = 223;
$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $areaId)->first();
if ($urg) {
    echo "URG FOUND: " . $urg->nombre . "\n";
    $areas = DB::table('0201sadpyrf_mar2026.areas')->get();
    $marArea = $areas->first(function($a) use ($urg) {
        if (trim($a->nombre) === trim($urg->nombre)) return true;
        if (stripos($a->nombre, $urg->nombre) !== false) return true;
        if (stripos($urg->nombre, $a->nombre) !== false) return true;
        return false;
    });
    if ($marArea) {
        echo "MAR AREA FOUND: " . $marArea->area_id . " - " . $marArea->nombre . "\n";
    } else {
        echo "MAR AREA NOT FOUND\n";
    }
} else {
    echo "URG NOT FOUND\n";
}
