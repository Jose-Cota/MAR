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
        Schema::create('riesgos_institucionales', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique(); // e.g., RI-2026-01
            $table->integer('ejercicio_id');
            $table->text('objetivo')->nullable();
            $table->text('riesgo');
            $table->text('factores')->nullable();
            $table->integer('probabilidad_sugerida')->nullable();
            $table->integer('impacto_sugerido')->nullable();
            $table->integer('probabilidad')->nullable();
            $table->integer('impacto')->nullable();
            $table->text('justificacion_valoracion')->nullable();
            $table->string('estatus')->default('Proyecto');
            $table->timestamps();
        });

        Schema::create('riesgo_institucional_fuente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riesgo_institucional_id')->constrained('riesgos_institucionales')->onDelete('cascade');
            $table->foreignId('riesgo_id')->constrained('riesgos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riesgo_institucional_fuente');
        Schema::dropIfExists('riesgos_institucionales');
    }
};
