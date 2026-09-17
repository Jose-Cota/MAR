<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app()->make(\App\Http\Controllers\Api\ProyectoController::class);
// Create a fake request
$request = \Illuminate\Http\Request::create('/api/proyectos/1455/ficha-descriptiva', 'GET');
// We need to bypass auth or mock it.
// Actually, let's just instantiate and call the private method using reflection
$reflection = new ReflectionClass(get_class($controller));
$method = $reflection->getMethod('getProyectoMetas');
$method->setAccessible(true);

$metas = $method->invokeArgs($controller, [1455]);
echo "Number of metas: " . count($metas) . "\n";
foreach ($metas as $m) {
    echo $m['tipo'] . " - " . $m['nombre'] . "\n";
}
