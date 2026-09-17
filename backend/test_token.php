<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    $user = User::where('usuario', 'jose.cota@tecdmx.org.mx')->first();
    if ($user) {
        echo "Found user ID: " . $user->usuario_poa_id . "\n";
        $token = $user->createToken('poa-frontend')->plainTextToken;
        echo "Token generated successfully!\n";
    } else {
        echo "User not found.\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
