<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1445)->first();
echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
