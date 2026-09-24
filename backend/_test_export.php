<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/export-db', 'GET');
$user = \App\Models\User::first();
$request->setUserResolver(function () use ($user) { return $user; });

$controller = $app->make(\App\Http\Controllers\Api\ExportDbController::class);
$response = $controller->export($request);

if ($response->getStatusCode() === 200) {
    echo "Export successful!\n";
    $data = json_decode($response->getContent(), true);
    echo "Years exported: " . implode(", ", array_keys($data['datos'])) . "\n";
} else {
    echo "Export failed with status: " . $response->getStatusCode() . "\n";
    echo $response->getContent() . "\n";
}
