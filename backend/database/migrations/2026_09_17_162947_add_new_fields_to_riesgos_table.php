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
        Schema::table('riesgos', function (Blueprint $table) {
            $table->text('efectos_consecuencias')->nullable();
            $table->text('factores_internos')->nullable();
            $table->text('factores_externos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riesgos', function (Blueprint $table) {
            $table->dropColumn('efectos_consecuencias');
            $table->dropColumn('factores_internos');
            $table->dropColumn('factores_externos');
        });
    }
};
