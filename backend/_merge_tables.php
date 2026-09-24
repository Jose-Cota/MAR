<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

DB::connection('poa_prod')->beginTransaction();

try {
    echo "1. Limpiando orphans en actividad_riesgo...\n";
    $deleted = DB::connection('poa_prod')->delete("
        DELETE ar FROM actividad_riesgo ar
        JOIN riesgos r ON ar.riesgo_id = r.id
        WHERE (r.ejercicio_id >= 19 AND ar.actividad_sustantiva_id NOT IN (SELECT id FROM actividades_sustantivas))
           OR (r.ejercicio_id < 19 AND ar.actividad_sustantiva_id NOT IN (SELECT accion_sustantiva_id FROM acciones_sustantivas))
    ");
    echo "   Borrados: $deleted\n";

    echo "2. Desplazando IDs de actividades_sustantivas (+50000)...\n";
    // Solo actualizar las que existen
    $actividades = DB::connection('poa_prod')->table('actividades_sustantivas')->get();
    foreach ($actividades as $act) {
        $new_id = $act->id + 50000;
        
        // Disable foreign key checks if any
        DB::connection('poa_prod')->statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Update the ID
        DB::connection('poa_prod')->table('actividades_sustantivas')->where('id', $act->id)->update(['id' => $new_id]);
        
        // Update actividad_riesgo ONLY for 2027 risks pointing to this ID
        DB::connection('poa_prod')->update("
            UPDATE actividad_riesgo ar
            JOIN riesgos r ON ar.riesgo_id = r.id
            SET ar.actividad_sustantiva_id = ?
            WHERE ar.actividad_sustantiva_id = ? AND r.ejercicio_id >= 19
        ", [$new_id, $act->id]);
        
        DB::connection('poa_prod')->statement('SET FOREIGN_KEY_CHECKS=1;');
    }
    echo "   Desplazadas: " . count($actividades) . "\n";

    echo "3. Copiando acciones_sustantivas a actividades_sustantivas...\n";
    $acciones = DB::connection('poa_prod')->table('acciones_sustantivas')->get();
    foreach ($acciones as $acc) {
        DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
            'id' => $acc->accion_sustantiva_id, // Mantener su ID original (1 a ~18000)
            'proyecto_id' => $acc->proyecto_id,
            'numero' => $acc->numero,
            'descripcion' => $acc->descripcion,
            'recursos_asociados' => $acc->recursos_asociados,
            'es_resumida' => $acc->es_resumida
        ]);
    }
    echo "   Copiadas: " . count($acciones) . "\n";

    DB::connection('poa_prod')->commit();
    echo "MERGE EXITOSO.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
