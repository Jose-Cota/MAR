<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('poa_prod')->select("SELECT * FROM bd_11Mayo2026.catAreas WHERE area LIKE '%Pleno%'");
echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
