<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pei_programas', function (Blueprint $table) {
            $table->bigIncrements('pei_programa_id');
            $table->unsignedSmallInteger('ejercicio_anio')->unique();
            $table->string('nombre', 255);
        });

        Schema::create('pei_lineas_estrategicas', function (Blueprint $table) {
            $table->bigIncrements('pei_linea_estrategica_id');
            $table->unsignedBigInteger('pei_programa_id');
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 255);

            $table->unique(['pei_programa_id', 'numero'], 'pei_lineas_programa_numero_unique');
            $table->foreign('pei_programa_id')
                ->references('pei_programa_id')
                ->on('pei_programas')
                ->onDelete('cascade');
        });

        Schema::create('pei_objetivos_estrategicos', function (Blueprint $table) {
            $table->bigIncrements('pei_objetivo_estrategico_id');
            $table->unsignedBigInteger('pei_linea_estrategica_id');
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 255);

            $table->unique(['pei_linea_estrategica_id', 'numero'], 'pei_objetivos_linea_numero_unique');
            $table->foreign('pei_linea_estrategica_id')
                ->references('pei_linea_estrategica_id')
                ->on('pei_lineas_estrategicas')
                ->onDelete('cascade');
        });

        Schema::create('pei_proyecto_alineaciones', function (Blueprint $table) {
            $table->bigIncrements('pei_proyecto_alineacion_id');
            $table->unsignedInteger('proyecto_id')->unique();
            $table->unsignedBigInteger('pei_programa_id');
            $table->unsignedBigInteger('pei_linea_estrategica_id');
            $table->unsignedBigInteger('pei_objetivo_estrategico_id');

            $table->foreign('pei_programa_id')
                ->references('pei_programa_id')
                ->on('pei_programas')
                ->onDelete('cascade');
            $table->foreign('pei_linea_estrategica_id')
                ->references('pei_linea_estrategica_id')
                ->on('pei_lineas_estrategicas')
                ->onDelete('cascade');
            $table->foreign('pei_objetivo_estrategico_id')
                ->references('pei_objetivo_estrategico_id')
                ->on('pei_objetivos_estrategicos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pei_proyecto_alineaciones');
        Schema::dropIfExists('pei_objetivos_estrategicos');
        Schema::dropIfExists('pei_lineas_estrategicas');
        Schema::dropIfExists('pei_programas');
    }
};
