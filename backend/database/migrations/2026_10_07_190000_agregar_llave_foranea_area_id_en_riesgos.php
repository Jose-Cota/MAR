<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enlaza riesgos con sus catálogos mediante llaves foráneas:
 *  - riesgos.area_id (varchar -> int)  => areas.area_id
 *  - riesgos.ejercicio_id (int)        => ejercicios.ejercicio_id
 *
 * Solo modifica la estructura; no cambia ningún dato.
 */
return new class extends Migration
{
    private const LLAVE_AREA = 'riesgos_area_id_foreign';

    private const LLAVE_EJERCICIO = 'riesgos_ejercicio_id_foreign';

    public function up(): void
    {
        if (! $this->existenTablasRequeridas()) {
            return;
        }

        $this->validarIntegridadPrevia();

        Schema::table('riesgos', function (Blueprint $tabla) {
            $tabla->integer('area_id')->nullable()->change();
        });

        Schema::table('riesgos', function (Blueprint $tabla) {
            $tabla->foreign('area_id', self::LLAVE_AREA)
                ->references('area_id')
                ->on('areas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $tabla->foreign('ejercicio_id', self::LLAVE_EJERCICIO)
                ->references('ejercicio_id')
                ->on('ejercicios')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('riesgos')) {
            return;
        }

        Schema::table('riesgos', function (Blueprint $tabla) {
            $tabla->dropForeign(self::LLAVE_AREA);
            $tabla->dropForeign(self::LLAVE_EJERCICIO);
        });

        Schema::table('riesgos', function (Blueprint $tabla) {
            $tabla->string('area_id')->nullable()->change();
        });
    }

    private function existenTablasRequeridas(): bool
    {
        return Schema::hasTable('riesgos')
            && Schema::hasTable('areas')
            && Schema::hasTable('ejercicios');
    }

    /**
     * Aborta si algún riesgo tiene area_id o ejercicio_id sin correspondencia
     * (la llave foránea fallaría y no deben corregirse datos automáticamente).
     */
    private function validarIntegridadPrevia(): void
    {
        $areasHuerfanas = DB::table('riesgos')
            ->whereNotNull('area_id')
            ->where(function ($consulta) {
                $consulta->whereRaw("area_id NOT REGEXP '^[0-9]+$'")
                    ->orWhereRaw('CAST(area_id AS UNSIGNED) NOT IN (SELECT area_id FROM areas)');
            })
            ->count();

        $ejerciciosHuerfanos = DB::table('riesgos')
            ->whereNotNull('ejercicio_id')
            ->whereRaw('ejercicio_id NOT IN (SELECT ejercicio_id FROM ejercicios)')
            ->count();

        if ($areasHuerfanas > 0 || $ejerciciosHuerfanos > 0) {
            throw new RuntimeException(
                "Migración abortada: {$areasHuerfanas} riesgos con area_id y "
                . "{$ejerciciosHuerfanos} con ejercicio_id sin correspondencia."
            );
        }
    }
};
