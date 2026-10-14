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
        Schema::table('riesgos_institucionales', function (Blueprint $table) {
            $table->text('control')->nullable()->after('factores');
            $table->text('indicador')->nullable()->after('control');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riesgos_institucionales', function (Blueprint $table) {
            $table->dropColumn(['control', 'indicador']);
        });
    }
};
