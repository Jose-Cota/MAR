<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$oldId = 1;
$nuevoEjercicioId = 2;
$nuevoEjercicioAnio = 2027;

$mapPg = [1 => 101, 2 => 102, 3 => 103, 4 => 104, 5 => 105, 6 => 106, 7 => 107];
$subprogramas = DB::connection('poa_prod')->table('subprogramas')->whereIn('programa_id', array_keys($mapPg))->get();

$count = 0;
foreach ($subprogramas as $sp) {
    $oldAlineacionesSp = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
        ->where('subprograma_id', $sp->subprograma_id)
        ->get();
    $count += $oldAlineacionesSp->count();
}

echo "Total old alineaciones found with subprogramas: $count\n";

