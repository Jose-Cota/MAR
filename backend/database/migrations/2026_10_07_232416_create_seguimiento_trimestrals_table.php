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
        Schema::create('seguimiento_trimestrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('riesgo_id');
            $table->integer('ejercicio_id');
            $table->integer('trimestre');
            
            // 6 campos para N y D
            $table->decimal('m1_n', 10, 2)->nullable();
            $table->decimal('m1_d', 10, 2)->nullable();
            $table->decimal('m2_n', 10, 2)->nullable();
            $table->decimal('m2_d', 10, 2)->nullable();
            $table->decimal('m3_n', 10, 2)->nullable();
            $table->decimal('m3_d', 10, 2)->nullable();

            $table->text('notas')->nullable();
            $table->string('estatus')->default('Abierto'); // Abierto, Validado

            $table->timestamps();

            $table->foreign('riesgo_id')->references('id')->on('riesgos')->onDelete('cascade');
            $table->unique(['riesgo_id', 'ejercicio_id', 'trimestre'], 'idx_riesgo_ejer_trim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_trimestrals');
    }
};
