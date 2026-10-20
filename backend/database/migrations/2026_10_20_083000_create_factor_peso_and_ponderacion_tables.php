<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear catálogo factor_peso
        if (!Schema::hasTable('factor_peso')) {
            Schema::create('factor_peso', function (Blueprint $table) {
                $table->id();
                $table->integer('ejercicio'); // 2026, 2027
                $table->string('ur'); // Nombre de la UR
                $table->unsignedBigInteger('ur_id')->nullable(); // ID de área / UR (1 a 25)
                $table->decimal('factor_peso', 5, 2)->default(1);
                $table->timestamps();

                $table->index(['ejercicio', 'ur_id']);
            });
        }

        // 2. Agregar columna ponderacion a riesgos_institucionales
        if (Schema::hasTable('riesgos_institucionales') && !Schema::hasColumn('riesgos_institucionales', 'ponderacion')) {
            Schema::table('riesgos_institucionales', function (Blueprint $table) {
                $table->decimal('ponderacion', 10, 4)->nullable()->after('impacto');
            });
        }

        // 3. Agregar columna puntaje a riesgos
        if (Schema::hasTable('riesgos') && !Schema::hasColumn('riesgos', 'puntaje')) {
            Schema::table('riesgos', function (Blueprint $table) {
                $table->decimal('puntaje', 10, 2)->nullable()->after('impacto');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('riesgos') && Schema::hasColumn('riesgos', 'puntaje')) {
            Schema::table('riesgos', function (Blueprint $table) {
                $table->dropColumn('puntaje');
            });
        }

        if (Schema::hasTable('riesgos_institucionales') && Schema::hasColumn('riesgos_institucionales', 'ponderacion')) {
            Schema::table('riesgos_institucionales', function (Blueprint $table) {
                $table->dropColumn('ponderacion');
            });
        }

        Schema::dropIfExists('factor_peso');
    }
};
