<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// We just bypass auth by instantiating the controller directly and replacing auth check or we just call the methods we need.
$controller = $app->make(\App\Http\Controllers\Api\ProyectoController::class);

$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('getActividades');
$method->setAccessible(true);
$actividades = $method->invoke($controller, 884);

echo "Actividades:\n";
echo json_encode($actividades) . "\n";

