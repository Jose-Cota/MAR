<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Api\ActividadSustantivaController::class);
$request = Illuminate\Http\Request::create('/api/actividades-sustantivas', 'GET', []);

$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $controller->index($request);
    echo "Actividades returned successfully: " . strlen($response->getContent()) . " bytes\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
