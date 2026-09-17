<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$lines = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->get();
foreach ($lines as $line) {
    if (strpos($line->nombre, 'Fortalecer los canales') !== false) {
        echo "Found Linea: {$line->pei_linea_estrategica_id} - {$line->numero} - {$line->nombre}\n";
    }
}
