<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$puestos = DB::select("SELECT * FROM bd_11Mayo2026.catPuestos LIMIT 1");
echo json_encode($puestos, JSON_PRETTY_PRINT);
