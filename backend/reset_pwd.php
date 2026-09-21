<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u = User::where('usuario', 'jose.cota')->first();
if ($u) {
    $u->password = Hash::make('password');
    $u->save();
    echo "Password updated successfully for " . $u->usuario . "\n";
} else {
    echo "User jose.cota not found.\n";
}
