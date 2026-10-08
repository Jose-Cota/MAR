<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('nombre', 'like', '%Cota%')
    ->orWhere('apellido_paterno', 'like', '%Cota%')
    ->orWhere('apellido_materno', 'like', '%Cota%')->first();

if ($user) {
    if (!$user->hasRole('Administrador')) {
        $user->assignRole('Administrador');
        echo "Rol Administrador asignado a: " . $user->name . "\n";
    } else {
        echo "El usuario ya tenia el rol Administrador: " . $user->name . "\n";
    }
} else {
    echo "Usuario no encontrado.\n";
}
