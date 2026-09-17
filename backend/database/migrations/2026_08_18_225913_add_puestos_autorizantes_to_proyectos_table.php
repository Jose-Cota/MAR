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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->string('puesto_responsable_ficha', 255)->nullable();
            $table->string('autorizante_nombre', 255)->nullable();
            $table->string('autorizante_puesto', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['puesto_responsable_ficha', 'autorizante_nombre', 'autorizante_puesto']);
        });
    }
};
