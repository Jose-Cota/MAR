<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$metas = DB::connection('poa_prod')
    ->table('metas as m')
    ->leftJoin('unidades_medidas as um', 'm.unidad_medida_id', '=', 'um.unidad_medida_id')
    ->select(
        'm.meta_id',
        'm.proyecto_id',
        'm.tipo',
        'm.nombre',
        'm.unidad_medida_id',
        'um.nombre as unidad_medida'
    )
    ->where('m.proyecto_id', 1455)
    ->orderBy('m.tipo')
    ->get();

print_r($metas);
