<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Api\UnidadResponsableController::class);
$request = Illuminate\Http\Request::create('/api/unidades-responsables', 'GET', []);

$user = \App\Models\User::where('email', 'admin@admin.com')->first();
if (!$user) $user = \App\Models\User::first();

// Mock hasRole
$user = new class extends \App\Models\User {
    public function hasRole($role) { return true; }
    public function getAttribute($key) { return null; }
    public function getAuthIdentifier() { return 1; }
};

$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);

    foreach ($data as $u) {
        echo $u['unidad_responsable_gasto_id'] . " | " . $u['numero'] . " | " . $u['nombre'] . "\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
