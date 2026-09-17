<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeiCatalogSeeder extends Seeder
{
    public function run()
    {
        // 1. Create PEI Program for current year if not exists
        $peiProgramaId = DB::table('pei_programas')->where('ejercicio_anio', 2026)->value('pei_programa_id');
        
        if (!$peiProgramaId) {
            $peiProgramaId = DB::table('pei_programas')->insertGetId([
                'ejercicio_anio' => 2026,
                'nombre' => 'Plan Estratégico Institucional 2026'
            ]);
        }

        // Clean existing tables to avoid duplicates if run multiple times
        DB::table('pei_lineas_estrategicas')->where('pei_programa_id', $peiProgramaId)->delete();

        // 2. Define the catalog based on the image
        // En la base actual: "linea" es el padre y "objetivo" es el hijo.
        // Por lo tanto, guardaremos el "Objetivo Estratégico" en la tabla pei_lineas_estrategicas
        // y la "Línea Estratégica" en la tabla pei_objetivos_estrategicos.
        $catalog = [
            [
                'numero' => 1,
                'nombre' => 'Objetivo Estratégico 1: Fortalecer la Percepción de un Tribunal cercano a la sociedad',
                'lineas' => [
                    [
                        'numero' => 'I',
                        'nombre' => 'Implementar proyectos de vinculación estratégica con actores sociales, como la sociedad civil organizada, instituciones académicas y organismos no gubernamentales, a nivel local y nacional.'
                    ],
                    [
                        'numero' => 'II',
                        'nombre' => 'Fortalecer los canales de acceso a la justicia electoral a la ciudadanía mediante espacios de diálogo, así como de participación, monitoreo y evaluación, que sean permanentes, abiertos y continuos.'
                    ],
                    [
                        'numero' => 'III',
                        'nombre' => 'Implementar una estrategia integral de comunicación y difusión para fortalecer la presencia del Tribunal ante la sociedad, mediante un modelo de datos abiertos en materia de justicia electoral.'
                    ]
                ]
            ],
            [
                'numero' => 2,
                'nombre' => 'Objetivo Estratégico 2: Brindar una impartición de justicia mediante sentencias en materia electoral y de participación ciudadana que garanticen una mayor oportunidad y claridad en sus resoluciones.',
                'lineas' => [
                    [
                        'numero' => 'I',
                        'nombre' => 'Fomentar la participación de la ciudadanía en el proceso judicial mediante mecanismos de retroalimentación, para que las resoluciones reflejen mejor las necesidades y expectativas de la sociedad.'
                    ],
                    [
                        'numero' => 'II',
                        'nombre' => 'Publicar versiones de las sentencias más relevantes de manera accesible y comprensible para toda persona, utilizando un lenguaje claro y sencillo, y asegurando que estén disponibles en plataformas digitales de fácil acceso.'
                    ],
                    [
                        'numero' => 'III',
                        'nombre' => 'Consolidar un modelo de Defensoría Pública que garantice una protección amplia de los derechos político-electorales para las personas que son parte de los grupos discriminados históricamente.'
                    ],
                    [
                        'numero' => 'IV',
                        'nombre' => 'Establecer mecanismos que permitan identificar necesidades, intercambiar información y coordinar los esfuerzos del Tribunal Electoral para atender a los grupos vulnerables.'
                    ],
                    [
                        'numero' => 'V',
                        'nombre' => 'Consolidar mecanismos de colaboración interinstitucional que fomenten el diálogo con los distintos poderes, organismos autónomos, autoridades electorales locales y nacionales, con el propósito de fortalecer el ejercicio de los derechos político-electorales de las personas y de difundir las decisiones relevantes en materia de justicia electoral.'
                    ]
                ]
            ],
            [
                'numero' => 3,
                'nombre' => 'Objetivo Estratégico 3: Ampliar el conocimiento y comprensión que la ciudadanía tiene de los mecanismos para la impartición de la justicia sobre los asuntos sometidos a competencia del Tribunal.',
                'lineas' => [
                    [
                        'numero' => 'I',
                        'nombre' => 'Incorporar el principio de máxima publicidad con un adecuado tratamiento de los datos personales en todos los actos, resoluciones y sentencias del Tribunal Electoral.'
                    ],
                    [
                        'numero' => 'II',
                        'nombre' => 'Implementar un modelo de sentencias breves, estructuradas y con lenguaje ciudadano e incluyente.'
                    ],
                    [
                        'numero' => 'III',
                        'nombre' => 'Analizar, evaluar y retroalimentar procesos, criterios y sentencias mediante mecanismos de colaboración con los actores y las organizaciones externas.'
                    ],
                    [
                        'numero' => 'IV',
                        'nombre' => 'Generar y mantener actualizado un repositorio de criterios y sentencias.'
                    ],
                    [
                        'numero' => 'V',
                        'nombre' => 'Adoptar buenas prácticas que permitan una adecuada protección de los datos personales en relación con la publicidad de las sentencias y los proyectos de resolución.'
                    ]
                ]
            ],
            [
                'numero' => 4,
                'nombre' => 'Objetivo Estratégico 4: Incrementar el nivel de integridad e identidad institucionales y de aprovechamiento del capital humano.',
                'lineas' => [
                    [
                        'numero' => 'I',
                        'nombre' => 'Fortalecer los esquemas de profesionalización, capacitación y evaluación continua de las personas servidoras públicas que integran el Tribunal.'
                    ],
                    [
                        'numero' => 'II',
                        'nombre' => 'Promover y fomentar una cultura de integridad de manera continua, con la aplicación de códigos de ética y conducta, reglas de integridad, capacitación, así como de denuncia y sanción por su incumplimiento.'
                    ],
                    [
                        'numero' => 'III',
                        'nombre' => 'Implementar actividades para fortalecer la identidad y el sentido de pertenencia institucional.'
                    ]
                ]
            ],
            [
                'numero' => 5,
                'nombre' => 'Objetivo Estratégico 5: Propiciar el cumplimiento oportuno y bajo los principios de eficacia y economía de todas las actividades y programas institucionales.',
                'lineas' => [
                    [
                        'numero' => 'I',
                        'nombre' => 'Fomentar una cultura de gestión por resultados.'
                    ],
                    [
                        'numero' => 'II',
                        'nombre' => 'Revisar y actualizar la normatividad interna y las estructuras orgánicas de manera continua para responder a las necesidades y circunstancias vigentes.'
                    ],
                    [
                        'numero' => 'III',
                        'nombre' => 'Simplificar, homologar, y, en su caso, actualizar o rediseñar los procesos que fortalezcan las actividades de control interno y administración de recursos y riesgos.'
                    ],
                    [
                        'numero' => 'IV',
                        'nombre' => 'Privilegiar y optimizar el desarrollo y la aplicación de herramientas y tecnologías de la información y comunicación en los procesos jurisdiccionales y administrativos, resguardando la seguridad de los datos personales, privilegiando el uso de los mejores estándares de seguridad informática.'
                    ],
                    [
                        'numero' => 'V',
                        'nombre' => 'Ejercer los recursos públicos en apego a los principios de buena administración, eficiencia, eficacia, economía, transparencia, honradez y rendición de cuentas en la administración.'
                    ]
                ]
            ]
        ];

        foreach ($catalog as $obj) {
            // Guardamos el Objetivo en la tabla pei_lineas_estrategicas (que actúa de padre)
            $parentId = DB::table('pei_lineas_estrategicas')->insertGetId([
                'pei_programa_id' => $peiProgramaId,
                'numero' => $obj['numero'],
                'nombre' => $obj['nombre']
            ]);

            foreach ($obj['lineas'] as $key => $linea) {
                // Guardamos la Línea en la tabla pei_objetivos_estrategicos (que actúa de hijo)
                // Convirtiendo el número romano a un entero temporalmente si la BD lo exige, 
                // pero según la migración 'numero' en pei_objetivos_estrategicos es unsignedTinyInteger.
                // Sin embargo, si es número romano (I, II), tinyInteger dará error.
                // Espera... la migración actual:
                // $table->unsignedTinyInteger('numero'); en pei_objetivos_estrategicos.
                // Oh! Si es tinyInteger, no puedo insertar "I", "II".
                // Insertaré el índice 1, 2, 3... en la columna numero y concatenaré el número romano al nombre.
                
                $roman = $linea['numero'];
                DB::table('pei_objetivos_estrategicos')->insert([
                    'pei_linea_estrategica_id' => $parentId,
                    'numero' => $key + 1,
                    'nombre' => $roman . '. ' . $linea['nombre']
                ]);
            }
        }
    }
}
