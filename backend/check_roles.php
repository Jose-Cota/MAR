<?php
$request = Illuminate\Http\Request::create('/api/usuarios', 'GET');
$request->setUserResolver(function() { return App\Models\User::where('usuario', 'jose.cota')->first(); });
$user = $request->user();
echo 'Roles count: ' . $user->roles->count() . PHP_EOL;
echo 'Roles json: ' . $user->roles->toJson() . PHP_EOL;
