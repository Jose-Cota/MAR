<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();

try {
    echo "3. Copiando acciones_sustantivas a actividades_sustantivas... (Bulk Insert)\n";
    
    // Encontrar qué IDs ya están en actividades_sustantivas (por si el script anterior insertó algunos)
    $existing = DB::connection('poa_prod')->table('actividades_sustantivas')->where('id', '<', 50000)->pluck('id')->toArray();
    
    $acciones = DB::connection('poa_prod')->table('acciones_sustantivas')->get();
    
    $chunks = array_chunk($acciones->toArray(), 500);
    $inserted = 0;
    
    foreach ($chunks as $chunk) {
        $data = [];
        foreach ($chunk as $acc) {
            if (in_array($acc->accion_sustantiva_id, $existing)) continue; // skip already inserted
            $data[] = [
                'id' => $acc->accion_sustantiva_id,
                'proyecto_id' => $acc->proyecto_id,
                'numero' => $acc->numero,
                'descripcion' => $acc->descripcion,
                'recursos_asociados' => $acc->recursos_asociados,
                'es_resumida' => $acc->es_resumida
            ];
        }
        if (!empty($data)) {
            DB::connection('poa_prod')->table('actividades_sustantivas')->insert($data);
            $inserted += count($data);
        }
    }
    
    echo "   Copiadas: $inserted\n";

    DB::connection('poa_prod')->commit();
    echo "MERGE EXITOSO.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
