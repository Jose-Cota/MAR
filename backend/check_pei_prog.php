<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$prog2027 = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2027)->first();
print_r($prog2027);

$aligns = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
    ->join('proyectos', 'pei_proyecto_alineaciones.proyecto_id', '=', 'proyectos.proyecto_id')
    ->where('proyectos.numero', '08')
    ->where('pei_proyecto_alineaciones.pei_programa_id', $prog2027->pei_programa_id)
    ->get();

print_r($aligns);
