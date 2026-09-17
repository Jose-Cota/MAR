<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Puesto;

class PuestosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla
        Puesto::truncate();

        // Obtener empleados y plazas de bd_11Mayo2026
        $empleadosControlaTE = DB::select("
            SELECT e.nombre, e.apellidoPaterno, e.apellidoMaterno, p.description as puesto_nombre
            FROM bd_11Mayo2026.catEmpleados e
            JOIN bd_11Mayo2026.catPlazas p ON e.idPlaza = p.id
        ");

        $usuariosPoa = User::all();

        $count = 0;
        foreach ($usuariosPoa as $usuario) {
            // Buscar empleado coincidente por nombre y apellidos (ignorando mayúsculas/minúsculas y acentos si es posible, pero haremos strtolower)
            $matched = null;
            foreach ($empleadosControlaTE as $emp) {
                // Función simple para limpiar acentos y comparar
                if ($this->limpiar($usuario->nombre) === $this->limpiar($emp->nombre) &&
                    $this->limpiar($usuario->apellido_paterno) === $this->limpiar($emp->apellidoPaterno) &&
                    $this->limpiar($usuario->apellido_materno) === $this->limpiar($emp->apellidoMaterno)) {
                    $matched = $emp;
                    break;
                }
            }

            if ($matched) {
                Puesto::create([
                    'usuario_poa_id' => $usuario->usuario_poa_id,
                    'nombre' => $matched->puesto_nombre
                ]);
                $count++;
            }
        }

        $this->command->info("Se han agregado {$count} puestos de empleados.");
    }

    private function limpiar($string)
    {
        if (!$string) return '';
        $string = trim($string);
        $string = mb_strtolower($string, 'UTF-8');
        // Reemplazar acentos
        $string = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $string
        );
        return $string;
    }
}
