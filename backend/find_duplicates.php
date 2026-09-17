<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Obtenemos usuarios
$users = DB::connection('poa_prod')->table('usuarios_poa')
    ->select('nombre', 'apellido_paterno', 'apellido_materno', 'usuario', 'usuario_poa_id', 'area_id', 'correo')
    ->get();

$groupsByName = [];
$groupsByUsernameBase = [];

foreach($users as $user) {
    // Group by full name
    $fullName = trim(strtolower($user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno));
    if (!isset($groupsByName[$fullName])) {
        $groupsByName[$fullName] = [];
    }
    $groupsByName[$fullName][] = $user;
    
    // Group by base username (e.g. jcota and jcota_ur2)
    // we take only the part before _, -, or digits at the end
    $baseUsername = preg_replace('/[_\-\d].*$/', '', strtolower($user->usuario));
    if (!isset($groupsByUsernameBase[$baseUsername])) {
        $groupsByUsernameBase[$baseUsername] = [];
    }
    $groupsByUsernameBase[$baseUsername][] = $user;
}

$duplicatesByName = array_filter($groupsByName, function($g) { return count($g) > 1; });

echo "=== Usuarios con el mismo Nombre Completo ===\n";
foreach($duplicatesByName as $name => $group) {
    echo strtoupper($name) . ":\n";
    foreach($group as $u) {
        echo "  - ID: {$u->usuario_poa_id} | Usuario: {$u->usuario} | UR_ID: {$u->area_id}\n";
    }
    echo "\n";
}

$duplicatesByBaseUsername = array_filter($groupsByUsernameBase, function($g) { return count($g) > 1; });

echo "=== Usuarios con 'Nombre de Usuario' similar ===\n";
foreach($duplicatesByBaseUsername as $base => $group) {
    // Filtramos los que tengan nombres completamente diferentes para evitar falsos positivos
    // (Ej: "mario" base de mario.lopez y mario.gomez no son duplicados)
    // Para simplificar, mostramos el grupo y el usuario juzgará
    if(count($group) > 5) continue; // Skip huge groups (e.g. common first names if they use first name as username)
    
    echo "Base Username [{$base}]:\n";
    foreach($group as $u) {
        $fn = $u->nombre . ' ' . $u->apellido_paterno;
        echo "  - ID: {$u->usuario_poa_id} | Usuario: {$u->usuario} | Nombre: {$fn} | UR_ID: {$u->area_id}\n";
    }
    echo "\n";
}
