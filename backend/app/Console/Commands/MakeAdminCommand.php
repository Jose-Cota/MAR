<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poa:make-admin {usuario : El nombre de usuario (ej. jose.cota)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna el rol de Administrador a un usuario especifico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('usuario');
        $user = User::where('usuario', $username)->first();

        if (!$user) {
            $this->error("Usuario '{$username}' no encontrado en la base de datos.");
            return 1;
        }

        // Asegurarse de que el rol exista
        $role = Role::firstOrCreate(['name' => 'Administrador']);

        // Asignar el rol
        $user->assignRole('Administrador');

        $this->info("El usuario '{$username}' ahora es Administrador del sistema y tiene acceso total.");
        return 0;
    }
}
