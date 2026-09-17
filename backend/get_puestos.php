<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$urgNombre = 'SECRETARÍA ADMINISTRATIVA';
$puestos = DB::select("
    SELECT DISTINCT p.puesto 
    FROM bd_11Mayo2026.catAreas a
    JOIN bd_11Mayo2026.catPlazas pl ON a.idArea = pl.idArea
    JOIN bd_11Mayo2026.catPuestos p ON pl.idPuesto = p.idPuesto
    WHERE a.area = ?
", [$urgNombre]);

echo json_encode($puestos, JSON_PRETTY_PRINT);
