<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$urgs = DB::connection('poa_prod')->select('SELECT * FROM unidades_responsables_gastos LIMIT 5');
echo json_encode($urgs, JSON_PRETTY_PRINT);
