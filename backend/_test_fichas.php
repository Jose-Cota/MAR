<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => '1']); // Presidencia catalog ID is 1
$controller = new App\Http\Controllers\Api\POAFichasController();
$response = $controller->getFichas($request);
echo json_encode($response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
