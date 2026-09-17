<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pei = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', 2027)->first();
if ($pei) {
    echo "PEI 2027 exists! ID: {$pei->pei_programa_id}\n";
    $lineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $pei->pei_programa_id)->get();
    foreach ($lineas as $l) {
        echo "Linea {$l->numero}: {$l->nombre}\n";
        $objetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->where('pei_linea_estrategica_id', $l->pei_linea_estrategica_id)->get();
        foreach ($objetivos as $o) {
            echo "  -> Obj {$o->numero}: {$o->nombre}\n";
        }
    }
} else {
    echo "PEI 2027 DOES NOT EXIST!\n";
}
