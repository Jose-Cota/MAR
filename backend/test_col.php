<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('poa_prod')->select("SHOW COLUMNS FROM proyectos LIKE 'puesto_responsable_ficha'");
echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
