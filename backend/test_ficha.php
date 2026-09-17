<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ProyectoController;

$controller = new ProyectoController();
// Fake request
$request = Illuminate\Http\Request::create('/api/proyectos/886/ficha-descriptiva', 'GET');
$user = App\Models\User::where('usuario', 'administrador')->first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $controller->fichaDescriptiva($request, 886);
echo "Response for 886 PEI Lines: \n";
$data = $response->getData();
print_r(count($data->pei->lineas));
