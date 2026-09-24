<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/riesgos', 'GET', ['ejercicio_id' => 2026, 'area_id' => 2]);

$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $controller = $app->make(\App\Http\Controllers\Api\RiesgoController::class);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Riesgos returned for area_id=2 (Presidencia): " . count($data) . "\n";
    if (count($data) > 0) {
        echo "First risk area_id: " . $data[0]['area_id'] . "\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
