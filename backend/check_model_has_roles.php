<?php
$user = App\Models\User::where('usuario', 'jose.cota')->first();
if ($user) {
    $roles = DB::table('model_has_roles')->where('model_id', $user->usuario_poa_id)->get();
    echo 'Roles for user: ' . $roles->count() . PHP_EOL;
    foreach ($roles as $r) echo 'Role ID: ' . $r->role_id . PHP_EOL;
}
