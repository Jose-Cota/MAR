<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pei = DB::connection('poa_prod')->table('pei_programas')->get();
echo "PEI Programas:\n";
foreach ($pei as $p) {
    echo "- ID: {$p->pei_programa_id}, Ejercicio: {$p->ejercicio_anio}, Nombre: {$p->nombre}\n";
    $lineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $p->pei_programa_id)->count();
    $objetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
        ->join('pei_lineas_estrategicas', 'pei_objetivos_estrategicos.pei_linea_estrategica_id', '=', 'pei_lineas_estrategicas.pei_linea_estrategica_id')
        ->where('pei_lineas_estrategicas.pei_programa_id', $p->pei_programa_id)->count();
    
    echo "  -> Lineas: {$lineas}, Objetivos: {$objetivos}\n";
}

