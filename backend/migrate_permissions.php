<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

// Clear all permissions
DB::table('role_has_permissions')->delete();
DB::table('permissions')->delete();
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$modules = [
    'module_dashboard',
    'module_proyectos',
    'module_unidades_administrativas',
    'module_responsables_operativos',
    'module_programas',
    'module_subprogramas',
    'module_unidades_medida',
    'module_tablero_administracion',
    'module_reportes_elaboracion',
    'module_reportes_seguimiento',
    'module_config_elaboracion',
    'module_config_seguimiento',
    'module_config_anteproyecto',
    'module_admin_catalogos',
    'module_admin_usuarios',
    'module_admin_roles'
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
        'module_dashboard',
        'module_proyectos',
        'module_unidades_administrativas',
        'module_tablero_administracion',
        'module_reportes_elaboracion',
        'module_reportes_seguimiento'
    ]);
}

if ($capturador) {
    $capturador->syncPermissions([
        'module_dashboard',
        'module_proyectos',
        'module_unidades_administrativas',
        'module_reportes_elaboracion',
        'module_reportes_seguimiento'
    ]);
}

echo "Permissions migrated successfully.\n";
