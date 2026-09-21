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
        Schema::create('riesgo_seguimientos_tables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('riesgo_seguimientos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('riesgo_id');
            $table->integer('ejercicio_id');
            $table->integer('mes');
            $table->decimal('numerador', 12, 2)->nullable();
            $table->decimal('denominador', 12, 2)->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('riesgo_id')->references('id')->on('riesgos')->onDelete('cascade');
            $table->unique(['riesgo_id', 'mes']);
        });

        Schema::create('riesgo_evaluaciones_trimestrales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('riesgo_id');
            $table->integer('ejercicio_id');
            $table->integer('trimestre');
            $table->string('etiqueta')->nullable();
            $table->integer('probabilidad')->default(0);
            $table->integer('impacto')->default(0);
            $table->text('evidencia_control')->nullable();
            $table->text('incidencia')->nullable();
            $table->text('accion_mitigacion')->nullable();
            $table->string('estatus')->nullable();
            $table->string('responsable')->nullable();
            $table->timestamps();

            $table->foreign('riesgo_id')->references('id')->on('riesgos')->onDelete('cascade');
            $table->unique(['riesgo_id', 'trimestre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riesgo_evaluaciones_trimestrales');
        Schema::dropIfExists('riesgo_seguimientos_mensuales');
        Schema::dropIfExists('riesgo_seguimientos_tables');
    }
};
