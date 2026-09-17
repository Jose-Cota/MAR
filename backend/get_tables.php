<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select("SHOW TABLES FROM bd_11Mayo2026");
echo json_encode($tables, JSON_PRETTY_PRINT);
