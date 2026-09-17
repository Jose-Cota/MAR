<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subprograma_pei_alineaciones', function (Blueprint $table) {
            $table->id();
            // The exact definition in our current db for subprogramas primary key is usually 'subprograma_id'.
            // In the POA system, they use char(2) or smallInt, let's assume it maps to string or integer. 
            // In the DB, 'subprogramas' table usually has 'subprograma_id' (bigInt or Integer).
            $table->unsignedInteger('subprograma_id'); // Assuming integer
            
            // Remember: in the DB, pei_lineas_estrategicas actually holds the "Objetivos" (1,2,3,4,5)
            $table->unsignedBigInteger('pei_linea_estrategica_id');
            // And pei_objetivos_estrategicos holds the "Lineas" (I, II, III...)
            $table->unsignedBigInteger('pei_objetivo_estrategico_id');

            $table->foreign('pei_linea_estrategica_id', 'fk_spa_linea_id')->references('pei_linea_estrategica_id')->on('pei_lineas_estrategicas')->onDelete('cascade');
            $table->foreign('pei_objetivo_estrategico_id', 'fk_spa_objetivo_id')->references('pei_objetivo_estrategico_id')->on('pei_objetivos_estrategicos')->onDelete('cascade');
            
            // Avoid duplicate mappings
            $table->unique(['subprograma_id', 'pei_linea_estrategica_id', 'pei_objetivo_estrategico_id'], 'unique_subprograma_pei_mapping');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subprograma_pei_alineaciones');
    }
};
