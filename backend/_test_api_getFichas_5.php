<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = new App\Http\Controllers\Api\POAFichasController();
$req = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => '2027', 'area_id' => '5']); // 5 = Recursos Humanos in 1-25 catalog
echo $controller->getFichas($req)->getContent();
