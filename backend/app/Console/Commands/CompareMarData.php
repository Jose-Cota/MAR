<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompareMarData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mar:compare {--ejercicio= : ID del ejercicio a comparar (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compara los registros de riesgos e indicadores entre la BD actual y bdMAR_alterna';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando comparación entre BD actual y bdMAR_alterna...');
        
        $ejercicioId = $this->option('ejercicio');

        // Riesgos
        $this->info('--- RIESGOS ---');
        $queryActual = DB::table('riesgos');
        $queryAlterna = DB::connection('mar_alterna')->table('riesgos');
        
        if ($ejercicioId) {
            $queryActual->where('ejercicio_id', $ejercicioId);
            $queryAlterna->where('ejercicio_id', $ejercicioId);
        }
        
        $riesgosActuales = $queryActual->get()->keyBy('id');
        $riesgosAlternos = $queryAlterna->get()->keyBy('id');

        $faltantesActuales = [];
        $diferenciasRiesgos = [];

        foreach ($riesgosAlternos as $id => $alterno) {
            if (!$riesgosActuales->has($id)) {
                $faltantesActuales[] = $id;
            } else {
                $actual = $riesgosActuales->get($id);
                // Compare some key fields
                if (
                    $actual->riesgo !== $alterno->riesgo || 
                    $actual->objetivo !== $alterno->objetivo ||
                    $actual->factores !== $alterno->factores
                ) {
                    $diferenciasRiesgos[] = $id;
                }
            }
        }

        $this->info("Riesgos en alterna que NO están en actual: " . count($faltantesActuales));
        if (count($faltantesActuales) > 0 && count($faltantesActuales) < 50) {
            $this->line("IDs faltantes: " . implode(', ', $faltantesActuales));
        }

        $this->info("Riesgos con diferencias (texto, objetivos, factores): " . count($diferenciasRiesgos));

        // Riesgo Indicadores (MAR module)
        $this->info('--- RIESGO INDICADORES (MAR) ---');
        $riActuales = DB::table('riesgo_indicadores')->get()->keyBy('id');
        $riAlternos = DB::connection('mar_alterna')->table('riesgo_indicadores')->get()->keyBy('id');

        $riFaltantes = [];
        foreach ($riAlternos as $id => $alterno) {
            if (!$riActuales->has($id)) {
                $riFaltantes[] = $id;
            }
        }

        $this->info("Riesgo_Indicadores en alterna que NO están en actual: " . count($riFaltantes));
        if (count($riFaltantes) > 0 && count($riFaltantes) < 50) {
            $this->line("IDs faltantes: " . implode(', ', $riFaltantes));
        }

        // Indicadores (POA module)
        $this->info('--- INDICADORES (POA) ---');
        $indActuales = DB::table('indicadores')->get()->keyBy('id');
        $indAlternos = DB::connection('mar_alterna')->table('indicadores')->get()->keyBy('id');

        $indFaltantes = [];
        foreach ($indAlternos as $id => $alterno) {
            if (!$indActuales->has($id)) {
                $indFaltantes[] = $id;
            }
        }

        $this->info("Indicadores (POA) en alterna que NO están en actual: " . count($indFaltantes));
        if (count($indFaltantes) > 0 && count($indFaltantes) < 50) {
            $this->line("IDs faltantes: " . implode(', ', $indFaltantes));
        }

        $this->info('Comparación finalizada. No se ha modificado ningún dato.');
    }
}
