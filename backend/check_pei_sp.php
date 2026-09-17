<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->count();
echo "Total in subprograma_pei_alineaciones: {$total}\n";

$alineaciones = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
    ->join('subprogramas', 'subprograma_pei_alineaciones.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->select('subprogramas.numero', 'subprograma_pei_alineaciones.pei_linea_estrategica_id')
    ->take(5)
    ->get();

foreach ($alineaciones as $al) {
    echo "Subprograma {$al->numero} -> Linea {$al->pei_linea_estrategica_id}\n";
}
