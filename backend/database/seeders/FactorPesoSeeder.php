<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactorPesoSeeder extends Seeder
{
    public function run(): void
    {
        $urs = [
            1 => ['nombre' => 'Presidencia', 'peso' => 2],
            2 => ['nombre' => 'Unidad Especializada de Procedimientos Sancionadores', 'peso' => 2],
            3 => ['nombre' => 'Coordinación de Vinculación y Relaciones Internacionales', 'peso' => 1],
            4 => ['nombre' => 'Dirección de Planeación y Recursos Financieros', 'peso' => 2],
            5 => ['nombre' => 'Dirección de Recursos Humanos', 'peso' => 2],
            6 => ['nombre' => 'Dirección de Recursos Materiales y Servicios Generales', 'peso' => 2],
            7 => ['nombre' => 'Secretaría General', 'peso' => 2],
            8 => ['nombre' => 'Secretaría Administrativa', 'peso' => 2],
            9 => ['nombre' => 'Contraloría Interna', 'peso' => 1],
            10 => ['nombre' => 'Dirección General Jurídica', 'peso' => 1],
            11 => ['nombre' => 'Instituto de Formación y Capacitación', 'peso' => 1],
            12 => ['nombre' => 'Unidad de Estadística y Jurisprudencia', 'peso' => 1],
            13 => ['nombre' => 'Unidad de Servicios Informáticos', 'peso' => 2],
            14 => ['nombre' => 'Coordinación de Comunicación Social y Relaciones Públicas', 'peso' => 1],
            15 => ['nombre' => 'Coordinación de Transparencia y Datos Personales', 'peso' => 1],
            16 => ['nombre' => 'Coordinación de Difusión y Publicación', 'peso' => 1],
            17 => ['nombre' => 'Comisión de Controversias Laborales y Administrativas', 'peso' => 1],
            18 => ['nombre' => 'Coordinación de Archivo', 'peso' => 1],
            19 => ['nombre' => 'Coordinación de Derechos Humanos y Género', 'peso' => 1],
            20 => ['nombre' => 'Defensoría Pública de Participación Ciudadana y de Procesos Democráticos', 'peso' => 2],
            21 => ['nombre' => 'Ponencia del Magistrado Armando Ámbriz Hernández', 'peso' => 2],
            22 => ['nombre' => 'Ponencia del Magistrado José Jesús Hernández Rodríguez', 'peso' => 2],
            23 => ['nombre' => 'Ponencia del Magistrado Osiris Vázquez Rangel', 'peso' => 2],
            24 => ['nombre' => 'Ponencia de la Magistrada Karina Salgado Lunar', 'peso' => 2],
            25 => ['nombre' => 'Ponencia de la Magistrada Laura Patricia Jiménez Castillo', 'peso' => 2],
        ];

        $ejercicios = [2026, 2027];

        foreach ($ejercicios as $ejercicio) {
            foreach ($urs as $id => $info) {
                DB::table('factor_peso')->updateOrInsert(
                    [
                        'ejercicio' => $ejercicio,
                        'ur_id' => $id,
                    ],
                    [
                        'ur' => $info['nombre'],
                        'factor_peso' => $info['peso'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Actualizar puntaje en riesgos
        // Mapeo ejercicio_id -> año
        $ejMap = [
            17 => 2026,
            19 => 2027
        ];

        $riesgos = DB::table('riesgos')->get();
        foreach ($riesgos as $r) {
            $year = $ejMap[$r->ejercicio_id] ?? 2026;
            $peso = DB::table('factor_peso')
                ->where('ejercicio', $year)
                ->where('ur_id', $r->area_id)
                ->value('factor_peso') ?? 1;

            $prob = $r->probabilidad ?? 0;
            $imp = $r->impacto ?? 0;
            $puntaje = $prob * $imp * $peso;

            DB::table('riesgos')
                ->where('id', $r->id)
                ->update(['puntaje' => $puntaje]);
        }
    }
}
