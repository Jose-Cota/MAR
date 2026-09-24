<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['area_id' => 10, 'ejercicio_id' => '2027']);
$controller = $app->make(App\Http\Controllers\Api\POAFichasController::class);
$response = $controller->getFichas($request);
echo json_encode($response->getData());
