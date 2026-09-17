<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('poa:migrate-security')]
#[Description('Migrates legacy users, roles and permissions to modern standard')]
class MigrateSecurityCommand extends Command
{
    public function handle()
    {
        $this->info('Starting Security Migration...');

        // 1. Migrate Roles (s_perfiles)
        $this->info('Migrating Roles...');
        $perfiles = \Illuminate\Support\Facades\DB::table('s_perfiles')->get();
        foreach ($perfiles as $perfil) {
            \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => $perfil->perfil,
                'guard_name' => 'web'
            ]);
        }

        // 2. Migrate Permissions (s_modulos)
        $this->info('Migrating Permissions...');
        $modulos = \Illuminate\Support\Facades\DB::table('s_modulos')->get();
        foreach ($modulos as $modulo) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $modulo->modulo,
                'guard_name' => 'web'
            ]);
        }

        // 3. Migrate Users (s_control + g_registros -> usuarios_poa)
        $this->info('Migrating Users...');
        $users = \Illuminate\Support\Facades\DB::table('s_control')
            ->join('g_registros', 's_control.nsf', '=', 'g_registros.nsf')
            ->get();

        foreach ($users as $legacyUser) {
            $user = \App\Models\User::updateOrCreate(
                ['usuario' => $legacyUser->usuario],
                [
                    'area_id' => $legacyUser->area_id ?? 0,
                    'nombre' => $legacyUser->nombre,
                    'apellido_paterno' => $legacyUser->apellido ?? '',
                    'apellido_materno' => $legacyUser->segundo_apellido ?? '',
                    'sexo' => $legacyUser->genero == 1 ? 'hombre' : 'mujer',
                    'password' => $legacyUser->password,
                    'foto' => '',
                    'nivel' => 'normal',
                    'ejercicio_elaboracion' => 0,
                    'consulta_integral' => 'no',
                    'captura_seguimiento' => 'no'
                ]
            );

            // 4. Assign Roles based on g_registros perfil
            if ($legacyUser->perfil == 1) {
                $user->assignRole('Administrador');
            } elseif ($legacyUser->perfil == 2) {
                $user->assignRole('Normal');
            }

            // 5. Assign Permissions based on s_privilegios
            $privilegios = \Illuminate\Support\Facades\DB::table('s_privilegios')
                ->where('nsf', $legacyUser->nsf)
                ->join('s_modulos', 's_privilegios.id_modulo', '=', 's_modulos.id_modulo')
                ->get();
            
            foreach ($privilegios as $priv) {
                $user->givePermissionTo($priv->modulo);
            }
        }

        $this->info('Migration completed successfully!');
    }
}
