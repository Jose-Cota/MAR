<?php
config(['auth.defaults.guard' => 'sanctum']);
$user = App\Models\User::where('usuario', 'jose.cota')->first();
echo 'Roles count: ' . $user->roles->count() . PHP_EOL;
