<?php
$request = Illuminate\Http\Request::create('/api/riesgos?ejercicio_id=2027', 'GET');
$controller = new App\Http\Controllers\Api\RiesgoController();
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);
echo 'API returned risks count: ' . count($data) . PHP_EOL;
