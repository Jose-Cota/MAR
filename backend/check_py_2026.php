<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos2026 = DB::connection('poa_prod')->table('proyectos')
    ->where('ejercicio_id', 17)
    ->get();

$count = 0;
foreach ($proyectos2026 as $py) {
    $al = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $py->proyecto_id)->first();
    if ($al) {
        $count++;
    }
}
echo "Total proyectos 2026 with pei_proyecto_alineaciones: $count / " . count($proyectos2026) . "\n";
