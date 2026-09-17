<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateIndicador extends Command
{
    protected $signature = 'app:update-indicador';
    protected $description = 'Update indicador name';

    public function handle()
    {
        // For project 1445 (proyecto 28, ur 11, ejercicio 2027)
        // Find the indicator currently named "Prueba de indicador #1"
        $indicador = DB::connection('poa_prod')->table('indicadores')
            ->where('proyecto_id', 1445)
            ->where('nombre', 'Prueba de indicador #1')
            ->first();

        if ($indicador) {
            DB::connection('poa_prod')->table('indicadores')
                ->where('indicador_id', $indicador->indicador_id)
                ->update([
                    'nombre' => 'Avance en la supervisión y el aseguramiento de la disponibilidad de los sistemas, portales e infraestructura que se encuentran en producción.'
                ]);
            $this->info("Indicador actualizado exitosamente.");
        } else {
            $this->error("No se encontró el indicador 'Prueba de indicador #1' en el proyecto 1445.");
            
            // let's show all indicators for this project to debug
            $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', 1445)->get();
            foreach($inds as $i) {
                $this->line("ID: " . $i->indicador_id . " | Nombre: " . $i->nombre);
            }
        }
    }
}
