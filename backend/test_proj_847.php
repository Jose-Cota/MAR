<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indsPy = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', 847)->get();
echo "Indicadores (by proyecto_id):\n";
foreach($indsPy as $i) {
    echo " - ID: $i->indicador_id, meta_id: $i->meta_id, id_metac: $i->id_metac\n";
}

$metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', 847)->get();
echo "\nMetas:\n";
foreach($metas as $m) {
    echo " - ID: $m->meta_id, tipo: $m->tipo\n";
}
