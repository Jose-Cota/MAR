<?php
$user = App\Models\User::where('usuario', 'jose.cota')->first();
echo $user->hasRole('Super Administrador') ? 'Tiene rol' : 'No tiene rol';
echo PHP_EOL;

$controller = new App\Http\Controllers\Api\UsuarioController();
$request = Illuminate\Http\Request::create('/api/usuarios', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });
$response = $controller->index($request);
echo 'Response status: ' . $response->getStatusCode() . PHP_EOL;
echo 'Response content length: ' . strlen($response->getContent()) . PHP_EOL;
echo 'Response start: ' . substr($response->getContent(), 0, 200) . PHP_EOL;
