<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$puestos = DB::select("
    SELECT DISTINCT n.nombrePuesto
    FROM bd_11Mayo2026.catAreas a
    JOIN bd_11Mayo2026.nominaTabuladoresDetalle n ON a.idArea = n.idArea
    WHERE a.area = 'SECRETARÍA ADMINISTRATIVA'
");
echo json_encode($puestos, JSON_PRETTY_PRINT);
