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
        Schema::create('usuario_unidad_responsable', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_poa_id');
            $table->unsignedBigInteger('unidad_responsable_gasto_id');
            
            $table->primary(['usuario_poa_id', 'unidad_responsable_gasto_id'], 'usr_urg_primary');
        });

        // Migrate existing data from usuarios_poa.area_id
        $users = \Illuminate\Support\Facades\DB::table('usuarios_poa')->whereNotNull('area_id')->get();
        foreach ($users as $user) {
            \Illuminate\Support\Facades\DB::table('usuario_unidad_responsable')->insertOrIgnore([
                'usuario_poa_id' => $user->usuario_poa_id,
                'unidad_responsable_gasto_id' => $user->area_id
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_unidad_responsable');
    }
};
