<?php
$user = App\Models\User::where('usuario', 'jose.cota')->first();
$token = $user->createToken('test')->plainTextToken;
echo 'TOKEN: ' . $token . PHP_EOL;
