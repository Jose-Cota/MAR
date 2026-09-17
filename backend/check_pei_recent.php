<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

for ($ejId = 15; $ejId <= 18; $ejId++) {
    $count = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
        ->join('subprogramas', 'subprograma_pei_alineaciones.subprograma_id', '=', 'subprogramas.subprograma_id')
        ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
        ->where('programas.ejercicio_id', $ejId)
        ->count();
    $anio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio_id', $ejId)->value('ejercicio');
    echo "Total subprograma_pei_alineaciones in $anio ($ejId): {$count}\n";
}
