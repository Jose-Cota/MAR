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
        Schema::table('operacion_ejercicio_ur', function (Blueprint $table) {
            $table->dateTime('fecha_prorroga')->nullable()->after('habilitado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operacion_ejercicio_ur', function (Blueprint $table) {
            $table->dropColumn('fecha_prorroga');
        });
    }
};
