<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Api\UnidadResponsableController::class);
$request = Illuminate\Http\Request::create('/api/unidades-responsables', 'GET', []);

// Mock user since it's required by the controller
$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

foreach ($data as $u) {
    echo $u['unidad_responsable_gasto_id'] . " | " . $u['numero'] . " | " . $u['nombre'] . "\n";
}
