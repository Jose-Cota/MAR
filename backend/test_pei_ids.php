<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sp = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->first();
echo "Alignment ID: {$sp->pei_linea_estrategica_id}\n";
$linea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_linea_estrategica_id', $sp->pei_linea_estrategica_id)->first();
echo "Linea Programa ID: {$linea->pei_programa_id}\n";

$prog2 = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2026)->first();
echo "2026 Programa ID: {$prog2->pei_programa_id}\n";
