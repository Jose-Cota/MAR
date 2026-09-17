<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$empleados = DB::select("SELECT * FROM bd_11Mayo2026.catEmpleados LIMIT 1");
echo json_encode($empleados, JSON_PRETTY_PRINT);
