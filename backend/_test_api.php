<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', [
    'ejercicio' => '2026',
    'area_id' => '4'
]);

$controller = new App\Http\Controllers\Api\POAFichasController();
$response = $controller->getFichas($request);
echo json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
