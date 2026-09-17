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
        Schema::table('pei_proyecto_alineaciones', function (Blueprint $table) {
            $table->dropUnique(['proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pei_proyecto_alineaciones', function (Blueprint $table) {
            $table->unique('proyecto_id');
        });
    }
};
