<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riesgos', function (Blueprint $table) {
            $table->id();
            $table->string('local_id')->nullable();
            $table->string('area_id')->nullable();
            $table->integer('ejercicio_id')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('riesgo')->nullable();
            $table->text('factores')->nullable();
            $table->integer('probabilidad')->default(0);
            $table->integer('impacto')->default(0);
            $table->integer('probabilidad_inicial')->default(0);
            $table->integer('impacto_inicial')->default(0);
            $table->string('status')->default('Borrador');
            $table->text('last_observation')->nullable();
            $table->timestamps();
        });

        Schema::create('riesgo_controles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riesgo_id')->constrained('riesgos')->onDelete('cascade');
            $table->text('texto');
            $table->string('evidencia_tipo')->nullable();
            $table->string('evidencia_referencia')->nullable();
            $table->string('evidencia_periodicidad')->nullable();
            $table->string('evidencia_responsable')->nullable();
            $table->string('evidencia_link')->nullable();
            $table->timestamps();
        });

        Schema::create('riesgo_indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riesgo_id')->constrained('riesgos')->onDelete('cascade');
            $table->string('nombre');
            $table->string('tipo')->nullable();
            $table->string('periodicidad')->nullable();
            $table->timestamps();
        });

        Schema::create('actividad_riesgo', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_sustantiva_id');
            $table->foreignId('riesgo_id')->constrained('riesgos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_riesgo');
        Schema::dropIfExists('riesgo_indicadores');
        Schema::dropIfExists('riesgo_controles');
        Schema::dropIfExists('riesgos');
    }
};
