<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => 7]); // Contraloria

$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) { return $user; });

try {
    $controller = $app->make(\App\Http\Controllers\Api\POAFichasController::class);
    $response = $controller->getFichas($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Fichas returned: " . count($data) . "\n";
    foreach ($data as $f) {
        echo "- " . $f['nombre'] . "\n";
        echo "  Acciones: " . count($f['acciones']) . "\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
