<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'configuracion_global', // Admin solo
            'administracion_usuarios', // Admin solo
            'catalogos_maestros', // Admin solo
            'ver_tablero', // Admin y Validador
            'ver_reportes_globales', // Admin y Validador
            'ver_reportes_area', // Capturador
            'planeacion_global', // Admin
            'planeacion_area', // Capturador y Validador (lectura)
            'captura_seguimiento' // Capturador
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Create Roles and assign created permissions
        $roleAdmin = Role::findOrCreate('Administrador', 'web');
        $roleAdmin->givePermissionTo(Permission::all());

        $roleValidador = Role::findOrCreate('Validador', 'web');
        $roleValidador->givePermissionTo([
            'ver_tablero',
            'ver_reportes_globales',
            'planeacion_area' // Para poder consultar los proyectos
        ]);

        $roleCapturador = Role::findOrCreate('Capturador', 'web');
        $roleCapturador->givePermissionTo([
            'ver_reportes_area',
            'planeacion_area',
            'captura_seguimiento'
        ]);

        // 3. Migrate existing users from `usuarios_poa` to Spatie roles
        $users = User::all();
        $adminCount = 0;
        $validadorCount = 0;
        $capturadorCount = 0;

        foreach ($users as $user) {
            // Remove any existing roles first just in case
            $user->syncRoles([]);
            $user->syncPermissions([]);

            if (strtolower(trim($user->nivel)) === 'administrador' || strtolower(trim($user->nivel)) === 'admin') {
                $user->assignRole($roleAdmin);
                $adminCount++;
            } else {
                // Determine if Validador or Capturador
                if (strtolower(trim($user->consulta_integral)) === 'si') {
                    $user->assignRole($roleValidador);
                    $validadorCount++;
                } else {
                    $user->assignRole($roleCapturador);
                    $capturadorCount++;
                }
            }
        }
        
        Log::info("Roles migrados correctamente: $adminCount Administradores, $validadorCount Validadores, $capturadorCount Capturadores.");
    }
}
