<?php
$user = \App\Models\User::where('usuario', 'fernando.cortes')->first();
if (!$user) {
    $user = \App\Models\User::where('email', 'fernando.cortes')->first();
}
if (!$user) {
    // maybe try to get the first user to see columns
    $first = \App\Models\User::first();
    echo "User not found. Columns are: \n";
    print_r(array_keys($first->toArray()));
    exit;
}

if ($user) {
    $user->assignRole('Super Administrador');
    echo "Role assigned successfully.\n";
}
