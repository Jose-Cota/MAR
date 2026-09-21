<?php
$request = Illuminate\Http\Request::create('/api/usuarios', 'GET');
$request->setUserResolver(function() { return App\Models\User::where('usuario', 'jose.cota')->first(); });
$controller = new App\Http\Controllers\Api\UsuarioController();
$response = $controller->index($request);
echo 'Response status: ' . $response->status() . PHP_EOL;
echo 'Response body: ' . substr($response->getContent(), 0, 200) . PHP_EOL;
