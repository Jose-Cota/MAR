<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$validPei = DB::connection('poa_prod')
    ->table('pei_proyecto_alineaciones as ppa')
    ->join('proyectos as py', 'py.proyecto_id', '=', 'ppa.proyecto_id')
    ->count();
echo "Valid PEI alignments in DB: $validPei\n";
