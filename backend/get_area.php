<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$areas = DB::select('SELECT * FROM bd_11Mayo2026.catAreas LIMIT 5');
echo json_encode($areas, JSON_PRETTY_PRINT);
