<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$obj1 = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('numero', 1)->first();
$lines = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->where('pei_linea_estrategica_id', $obj1->pei_linea_estrategica_id)->get();
foreach ($lines as $l) {
    echo "Linea: {$l->numero}, {$l->nombre}\n";
}
