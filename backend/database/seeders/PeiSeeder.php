<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeiSeeder extends Seeder
{
    public function run(): void
    {
        $estructura = [
            2024 => [
                'nombre' => 'Programa Estratégico Institucional 2024',
                'lineas' => [
                    [
                        'numero' => 1,
                        'nombre' => 'Planeación y coordinación institucional',
                        'objetivos' => [
                            ['numero' => 1, 'nombre' => 'Fortalecer la planeación institucional y el seguimiento de compromisos'],
                            ['numero' => 2, 'nombre' => 'Alinear programas, proyectos y recursos a las prioridades institucionales'],
                            ['numero' => 3, 'nombre' => 'Impulsar la evaluación de resultados y la mejora continua'],
                        ],
                    ],
                    [
                        'numero' => 2,
                        'nombre' => 'Transformación digital y simplificación de procesos',
                        'objetivos' => [
                            ['numero' => 1, 'nombre' => 'Digitalizar procesos sustantivos y de apoyo'],
                            ['numero' => 2, 'nombre' => 'Incrementar la interoperabilidad y disponibilidad de la información'],
                            ['numero' => 3, 'nombre' => 'Fortalecer la cultura digital y la seguridad de la información'],
                        ],
                    ],
                    [
                        'numero' => 3,
                        'nombre' => 'Gestión administrativa y presupuestal eficiente',
                        'objetivos' => [
                            ['numero' => 1, 'nombre' => 'Optimizar el uso del presupuesto y los recursos materiales'],
                            ['numero' => 2, 'nombre' => 'Mejorar los tiempos de respuesta en procesos administrativos'],
                            ['numero' => 3, 'nombre' => 'Fortalecer el control y la trazabilidad del gasto'],
                        ],
                    ],
                    [
                        'numero' => 4,
                        'nombre' => 'Transparencia, control interno y rendición de cuentas',
                        'objetivos' => [
                            ['numero' => 1, 'nombre' => 'Consolidar mecanismos de control interno y auditoría preventiva'],
                            ['numero' => 2, 'nombre' => 'Fortalecer la transparencia y la rendición de cuentas'],
                            ['numero' => 3, 'nombre' => 'Mejorar el seguimiento de observaciones y riesgos institucionales'],
                        ],
                    ],
                    [
                        'numero' => 5,
                        'nombre' => 'Desarrollo del talento humano y mejora continua',
                        'objetivos' => [
                            ['numero' => 1, 'nombre' => 'Desarrollar competencias del personal alineadas a la estrategia institucional'],
                            ['numero' => 2, 'nombre' => 'Impulsar el clima laboral, la colaboración y el liderazgo'],
                            ['numero' => 3, 'nombre' => 'Establecer prácticas de capacitación y aprendizaje continuo'],
                        ],
                    ],
                ],
            ],
            2025 => [
                'nombre' => 'Programa Estratégico Institucional 2025',
                'lineas' => [],
            ],
            2026 => [
                'nombre' => 'Programa Estratégico Institucional 2026',
                'lineas' => [],
            ],
            2027 => [
                'nombre' => 'Programa Estratégico Institucional 2027',
                'lineas' => [],
            ],
        ];

        foreach ([2025, 2026, 2027] as $anio) {
            $estructura[$anio]['lineas'] = $estructura[2024]['lineas'];
        }

        DB::transaction(function () use ($estructura) {
            foreach ($estructura as $anio => $programaData) {
                DB::table('pei_programas')->updateOrInsert(
                    ['ejercicio_anio' => $anio],
                    ['nombre' => $programaData['nombre']]
                );

                $programaId = DB::table('pei_programas')
                    ->where('ejercicio_anio', $anio)
                    ->value('pei_programa_id');

                foreach ($programaData['lineas'] as $lineaData) {
                    DB::table('pei_lineas_estrategicas')->updateOrInsert(
                        [
                            'pei_programa_id' => $programaId,
                            'numero' => $lineaData['numero'],
                        ],
                        ['nombre' => $lineaData['nombre']]
                    );

                    $lineaId = DB::table('pei_lineas_estrategicas')
                        ->where('pei_programa_id', $programaId)
                        ->where('numero', $lineaData['numero'])
                        ->value('pei_linea_estrategica_id');

                    foreach ($lineaData['objetivos'] as $objetivoData) {
                        DB::table('pei_objetivos_estrategicos')->updateOrInsert(
                            [
                                'pei_linea_estrategica_id' => $lineaId,
                                'numero' => $objetivoData['numero'],
                            ],
                            ['nombre' => $objetivoData['nombre']]
                        );
                    }
                }
            }
        });
    }
}
