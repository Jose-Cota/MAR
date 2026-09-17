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
        Schema::connection('poa_prod')->table('subprogramas', function (Blueprint $table) {
            $table->unsignedBigInteger('ejercicio_id')->nullable()->after('programa_id');
        });

        Schema::connection('poa_prod')->table('responsables_operativos', function (Blueprint $table) {
            $table->unsignedBigInteger('ejercicio_id')->nullable()->after('unidad_responsable_gasto_id');
        });

        Schema::connection('poa_prod')->table('proyectos', function (Blueprint $table) {
            $table->unsignedBigInteger('ejercicio_id')->nullable()->after('responsable_operativo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('poa_prod')->table('subprogramas', function (Blueprint $table) {
            $table->dropColumn('ejercicio_id');
        });

        Schema::connection('poa_prod')->table('responsables_operativos', function (Blueprint $table) {
            $table->dropColumn('ejercicio_id');
        });

        Schema::connection('poa_prod')->table('proyectos', function (Blueprint $table) {
            $table->dropColumn('ejercicio_id');
        });
    }
};
