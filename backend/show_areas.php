<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$areas = \Illuminate\Support\Facades\DB::select("SELECT id, nombre FROM unidades_responsables_gastos WHERE nombre LIKE '%Archivo%'");
print_r($areas);
