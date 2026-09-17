<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::with('roles', 'permissions', 'responsablesOperativos', 'unidadesResponsables')->where('usuario', 'analuisa.oliver')->first();
if ($user) {
    echo json_encode($user->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "User not found";
}
