<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pys = DB::connection('poa_prod')->table('proyectos')->where('nombre', 'like', '%Operación y control de pago de nóminas%')->get();
foreach($pys as $p) {
    $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $p->proyecto_id)->count();
    echo $p->proyecto_id . " => inds: " . $inds . "\n";
}
