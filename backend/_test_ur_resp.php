<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/unidades-responsables', 'GET', []);

$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $controller = $app->make(\App\Http\Controllers\Api\UnidadResponsableController::class);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    foreach ($data as $u) {
        if (stripos($u['nombre'], 'Presidencia') !== false) {
            echo "URG: " . $u['unidad_responsable_gasto_id'] . " | " . $u['nombre'] . " | mar_area_id: " . ($u['mar_area_id'] ?? 'NULL') . "\n";
        }
    }
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
