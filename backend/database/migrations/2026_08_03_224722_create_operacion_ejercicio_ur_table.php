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
        Schema::connection('poa_prod')->create('operacion_ejercicio_ur', function (Blueprint $table) {
            $table->id();
            $table->integer('operacion_ejercicio_id');
            $table->integer('unidad_responsable_gasto_id');
            $table->char('habilitado', 2)->default('si'); // si, no
            // No timestamps since legacy DB doesn't use them consistently, but we can add them if we want.
            // Let's add them just in case.
            $table->timestamps();

            // Foreign keys if necessary, but legacy poa_prod might not support strict foreign keys.
            // We'll leave it simple.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('poa_prod')->dropIfExists('operacion_ejercicio_ur');
    }
};
