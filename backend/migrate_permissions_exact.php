<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

// Clear all permissions again to be 100% exact
DB::table('role_has_permissions')->delete();
DB::table('permissions')->delete();
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$modules = [
    'Dashboard',
    'proyectos',
    'Unidades administrativas',
    'Responsables operativos',
    'Programas',
    'subprogramas',
    'Unidades de medida',
    'Tablero de administracion',
    'Reportes de Elaboracion',
    'Reportes de seguimiento',
    'Configuracion Elaboracion',
    'Configuracion Seguimiento',
    'Configuracion Anteproyecto',
    'Ejercicios',
    'Etapas',
    'Usuarios y accesos',
    'Roles y permisos' // Added so they don't lock themselves out
];

foreach ($modules as $mod) {
    Permission::create(['name' => $mod, 'guard_name' => 'web']);
}

$admin = Role::where('name', 'Administrador')->first();
$validador = Role::where('name', 'Validador')->first();
$capturador = Role::where('name', 'Capturador')->first();

if ($admin) {
    $admin->syncPermissions($modules);
}

if ($validador) {
    $validador->syncPermissions([
        'Dashboard',
        'proyectos',
        'Unidades administrativas',
        'Tablero de administracion',
        'Reportes de Elaboracion',
        'Reportes de seguimiento'
    ]);
}

if ($capturador) {
    $capturador->syncPermissions([
        'Dashboard',
        'proyectos',
        'Unidades administrativas',
        'Reportes de Elaboracion',
        'Reportes de seguimiento'
    ]);
}

echo "Permissions exact matched successfully.\n";
