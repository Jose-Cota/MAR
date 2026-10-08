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
        Schema::create('configuracion_trimestres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unidad_responsable_id')->nullable();
            $table->integer('ejercicio_id');
            $table->boolean('t1_abierto')->default(false);
            $table->boolean('t2_abierto')->default(false);
            $table->boolean('t3_abierto')->default(false);
            $table->boolean('t4_abierto')->default(false);
            $table->timestamps();

            $table->unique(['unidad_responsable_id', 'ejercicio_id'], 'cfg_trim_ur_ejer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_trimestres');
    }
};
