<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

$user = User::first(); 
$request = Request::create('/api/proyectos/884/ficha-descriptiva', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });

$controller = $app->make(\App\Http\Controllers\Api\ProyectoController::class);
$response = $controller->fichaDescriptiva($request, 884);

$content = json_decode($response->getContent(), true);
echo json_encode(array_keys($content)) . "\n";
if (isset($content['message'])) {
    echo "Message: " . $content['message'] . "\n";
}
