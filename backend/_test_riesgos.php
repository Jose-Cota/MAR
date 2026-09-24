<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/riesgos', 'GET', ['area_id' => 10, 'ejercicio_id' => '2027']);
$controller = $app->make(App\Http\Controllers\Api\RiesgoController::class);
$response = $controller->index($request);
echo json_encode($response->getData());
