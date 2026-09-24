<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/riesgos', 'GET', ['ejercicio_id' => 2026]);

$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $controller = $app->make(\App\Http\Controllers\Api\RiesgoController::class);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Total Riesgos returned: " . count($data) . "\n";
    $areasCounts = [];
    foreach ($data as $r) {
        $a = $r['area_id'];
        if (!isset($areasCounts[$a])) $areasCounts[$a] = 0;
        $areasCounts[$a]++;
    }
    print_r($areasCounts);
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
