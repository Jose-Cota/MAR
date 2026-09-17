<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::connection('poa_prod')->select("SELECT DISTINCT area FROM bd_11Mayo2026.catAreas");
echo json_encode(array_column($rows, 'area'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
