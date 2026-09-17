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
        Schema::table('proyecto_bitacoras', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
        });

        Schema::table('proyecto_bitacoras', function (Blueprint $table) {
            $table->foreign('user_id')->references('usuario_poa_id')->on('usuarios_poa')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyecto_bitacoras', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
