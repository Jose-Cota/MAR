<?php
$user = App\Models\User::where('usuario', 'jose.cota')->first();
echo 'User roles: ' . implode(', ', $user->roles->pluck('name')->toArray()) . PHP_EOL;
