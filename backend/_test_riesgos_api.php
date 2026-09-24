<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['area_id'] = 1;
$_GET['ejercicio_id'] = 2026;

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/riesgos', 'GET', $_GET);
$response = $kernel->handle($request);

echo $response->getContent();
