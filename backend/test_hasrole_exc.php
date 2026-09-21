<?php
try {
    $user = App\Models\User::where('usuario', 'jose.cota')->first();
    $res = $user->hasRole('Super Administrador');
    echo 'hasRole returned: ' . ($res ? 'true' : 'false') . PHP_EOL;
} catch (\Exception $e) {
    echo 'Exception caught: ' . get_class($e) . ' - ' . $e->getMessage() . PHP_EOL;
}
