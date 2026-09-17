<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total2026 = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
    ->join('subprogramas', 'subprograma_pei_alineaciones.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
    ->where('programas.ejercicio_id', 17) // 2026
    ->count();

echo "Total subprograma_pei_alineaciones in 2026: {$total2026}\n";
