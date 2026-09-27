<?php
$user = \App\Models\User::where('username', 'fernando.cortes')->first();
if ($user) {
    $user->assignRole('Super Administrador');
    echo "Role assigned successfully.\n";
} else {
    echo "User not found.\n";
}
