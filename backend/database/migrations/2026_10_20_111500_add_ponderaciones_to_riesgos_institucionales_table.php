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
        if (Schema::hasTable('riesgos_institucionales')) {
            Schema::table('riesgos_institucionales', function (Blueprint $table) {
                if (!Schema::hasColumn('riesgos_institucionales', 'ponderacion_probabilidad')) {
                    $table->decimal('ponderacion_probabilidad', 10, 4)->nullable()->after('ponderacion');
                }
                if (!Schema::hasColumn('riesgos_institucionales', 'ponderacion_impacto')) {
                    $table->decimal('ponderacion_impacto', 10, 4)->nullable()->after('ponderacion_probabilidad');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('riesgos_institucionales')) {
            Schema::table('riesgos_institucionales', function (Blueprint $table) {
                if (Schema::hasColumn('riesgos_institucionales', 'ponderacion_impacto')) {
                    $table->dropColumn('ponderacion_impacto');
                }
                if (Schema::hasColumn('riesgos_institucionales', 'ponderacion_probabilidad')) {
                    $table->dropColumn('ponderacion_probabilidad');
                }
            });
        }
    }
};
