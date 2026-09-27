<?php
$controller = app()->make(\App\Http\Controllers\Api\ExportDbController::class);
$request = new \Illuminate\Http\Request();
$response = $controller->export($request);
file_put_contents('c:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-25.json', $response->getContent());
echo "Backup created successfully.\n";
