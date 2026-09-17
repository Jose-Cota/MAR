<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sql = "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '0201sadpyrf_poa2026' AND (COLUMN_NAME LIKE '%urg%' OR COLUMN_NAME LIKE '%ur_%' OR COLUMN_NAME = 'ro' OR COLUMN_NAME = 'numero' OR COLUMN_NAME LIKE '%responsable_operativo%')";
$rows = DB::connection('poa_prod')->select($sql);

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
