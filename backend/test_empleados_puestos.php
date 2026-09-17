<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$urgNombre = 'SECRETARÍA ADMINISTRATIVA';
$puestos = DB::select("
    SELECT DISTINCT p.description as puesto
    FROM bd_11Mayo2026.catAreas a
    JOIN bd_11Mayo2026.catEmpleados e ON a.idArea = e.idArea
    JOIN bd_11Mayo2026.catPlazas p ON e.idPlaza = p.id
    WHERE a.area = ?
", [$urgNombre]);

echo json_encode($puestos, JSON_PRETTY_PRINT);
