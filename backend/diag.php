<?php

use Illuminate\Support\Facades\DB;

$user = DB::table('usuarios_poa')->where('usuario', 'jose.cota')->first();

if (!$user) {
    echo "Usuario jose.cota no encontrado." . PHP_EOL;
    exit;
}

echo "Usuario encontrado: {$user->nombre} {$user->apellido_paterno} (ID: {$user->usuario_poa_id})" . PHP_EOL;

// Roles actuales
$rolesActuales = DB::table('model_has_roles')
    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->where('model_has_roles.model_id', $user->usuario_poa_id)
    ->pluck('roles.name');

echo "Roles actuales: " . ($rolesActuales->count() ? implode(', ', $rolesActuales->toArray()) : 'ninguno') . PHP_EOL;

// Obtener o crear el rol "Super Administrador"
$rol = DB::table('roles')->where('name', 'Super Administrador')->first();
if (!$rol) {
    $rolId = DB::table('roles')->insertGetId([
        'name' => 'Super Administrador',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Rol 'Super Administrador' creado con ID: {$rolId}" . PHP_EOL;
} else {
    $rolId = $rol->id;
    echo "Rol 'Super Administrador' existe con ID: {$rolId}" . PHP_EOL;
}

// Eliminar roles anteriores y asignar Super Administrador
DB::table('model_has_roles')->where('model_id', $user->usuario_poa_id)->delete();
DB::table('model_has_roles')->insert([
    'role_id' => $rolId,
    'model_type' => 'App\\Models\\User',
    'model_id' => $user->usuario_poa_id,
]);

// Limpiar caché de permisos
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "✅ jose.cota ahora tiene el rol 'Super Administrador'." . PHP_EOL;
