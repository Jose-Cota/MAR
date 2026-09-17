<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$obj3 = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('numero', 3)->get();
echo "Obj 3 count: " . count($obj3) . "\n";
foreach($obj3 as $o) {
    echo "ID: {$o->pei_linea_estrategica_id}, Nombre: {$o->nombre}, Programa: {$o->pei_programa_id}\n";
    $lines = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->where('pei_linea_estrategica_id', $o->pei_linea_estrategica_id)->get();
    foreach ($lines as $l) {
        echo "  - Linea: {$l->numero}, {$l->nombre}\n";
    }
}
