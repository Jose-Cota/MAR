<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeiMappingSeeder extends Seeder
{
    public function run()
    {
        // Limpiamos la tabla de mapeo
        DB::table('subprograma_pei_alineaciones')->truncate();

        // Mapeo organizado por [Programa] => [ Subprogramas => [Objetivo, [Lineas]] ]
        $mapeos = [
            '01' => [
                [ 'subprogramas' => [1, 2, 3], 'objetivo' => 2, 'lineas' => ['I', 'II', 'III', 'IV', 'V'] ],
                [ 'subprogramas' => [1, 2], 'objetivo' => 3, 'lineas' => ['II', 'III'] ],
            ],
            '02' => [
                [ 'subprogramas' => [4, 5], 'objetivo' => 1, 'lineas' => ['I', 'III'] ],
                [ 'subprogramas' => [6], 'objetivo' => 1, 'lineas' => ['II'] ],
            ],
            '03' => [
                [ 'subprogramas' => [7, 8], 'objetivo' => 5, 'lineas' => ['III'] ],
                [ 'subprogramas' => [9], 'objetivo' => 5, 'lineas' => ['II'] ],
            ],
            '04' => [
                [ 'subprogramas' => [10, 11, 12], 'objetivo' => 5, 'lineas' => ['IV'] ],
            ],
            '05' => [
                [ 'subprogramas' => [13], 'objetivo' => 3, 'lineas' => ['I', 'V'] ],
                [ 'subprogramas' => [14], 'objetivo' => 3, 'lineas' => ['IV'] ],
            ],
            '06' => [
                [ 'subprogramas' => [15, 16, 17], 'objetivo' => 5, 'lineas' => ['I', 'V'] ],
            ],
            '07' => [
                [ 'subprogramas' => [18, 19], 'objetivo' => 4, 'lineas' => ['I', 'II', 'III'] ],
            ]
        ];

        foreach ($mapeos as $pg_numero => $reglas) {
            // Find all programas with this numero
            $programas = DB::table('programas')->where('numero', str_pad($pg_numero, 2, '0', STR_PAD_LEFT))->get();

            foreach ($programas as $pg) {
                foreach ($reglas as $regla) {
                    $dbObjetivo = DB::table('pei_lineas_estrategicas')->where('numero', $regla['objetivo'])->first();
                    if (!$dbObjetivo) continue;

                    foreach ($regla['lineas'] as $lineaNumRomano) {
                        $dbLinea = DB::table('pei_objetivos_estrategicos')
                            ->where('pei_linea_estrategica_id', $dbObjetivo->pei_linea_estrategica_id)
                            ->where('nombre', 'like', $lineaNumRomano . '.%')
                            ->first();
                        
                        if (!$dbLinea) continue;

                        foreach ($regla['subprogramas'] as $sp_numero) {
                            $sp = DB::table('subprogramas')
                                ->where('programa_id', $pg->programa_id)
                                ->where('numero', str_pad($sp_numero, 2, '0', STR_PAD_LEFT))
                                ->first();

                            if ($sp) {
                                DB::table('subprograma_pei_alineaciones')->updateOrInsert([
                                    'subprograma_id' => $sp->subprograma_id,
                                    'pei_linea_estrategica_id' => $dbObjetivo->pei_linea_estrategica_id,
                                    'pei_objetivo_estrategico_id' => $dbLinea->pei_objetivo_estrategico_id,
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
