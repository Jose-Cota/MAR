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
        if (Schema::hasTable('riesgos') && !Schema::hasColumn('riesgos', 'ponderacion')) {
            Schema::table('riesgos', function (Blueprint $table) {
                $table->decimal('ponderacion', 8, 2)->nullable()->after('puntaje');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('riesgos') && Schema::hasColumn('riesgos', 'ponderacion')) {
            Schema::table('riesgos', function (Blueprint $table) {
                $table->dropColumn('ponderacion');
            });
        }
    }
};
