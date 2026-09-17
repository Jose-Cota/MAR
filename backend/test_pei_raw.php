<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$any = DB::connection('poa_prod')
    ->table('pei_proyecto_alineaciones as ppa')
    ->join('proyectos as py', 'py.proyecto_id', '=', 'ppa.proyecto_id')
    ->select('py.proyecto_id', 'py.nombre', 'py.responsable_operativo_id', 'py.subprograma_id')
    ->take(5)
    ->get();
echo "Raw PEI alignments:\n" . json_encode($any) . "\n";
