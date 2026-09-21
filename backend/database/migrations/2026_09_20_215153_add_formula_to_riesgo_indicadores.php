<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('riesgo_indicadores', function (Blueprint $table) {
            $table->text('formula')->nullable();
            $table->string('unidad')->nullable();
            $table->string('sentido')->nullable();
            $table->text('numerador')->nullable();
            $table->text('denominador')->nullable();
        });
    }

    public function down()
    {
        Schema::table('riesgo_indicadores', function (Blueprint $table) {
            $table->dropColumn(['formula', 'unidad', 'sentido', 'numerador', 'denominador']);
        });
    }
};
