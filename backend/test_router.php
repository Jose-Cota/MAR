<?php
$request = Illuminate\Http\Request::create('/api/usuarios', 'GET');
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(function() { return App\Models\User::where('usuario', 'jose.cota')->first(); });
$controller = app()->make(Illuminate\Contracts\Http\Kernel::class);
$response = $controller->handle($request);
echo 'Response status via router: ' . $response->status() . PHP_EOL;
