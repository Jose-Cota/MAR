<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::all();
$count = 0;
foreach($users as $user) {
    if (!$user->hasRole("Capturador")) {
        $user->assignRole("Capturador");
    }
    if (!$user->hasRole("Validador")) {
        $user->assignRole("Validador");
    }
    $count++;
}
echo "Asignados roles a $count usuarios.\n";

