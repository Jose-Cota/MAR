<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $py_2027_id = 1421;
    
    // Get from acciones_sustantivas
    $acciones = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $py_2027_id)->get();
    
    foreach ($acciones as $a) {
        $a_arr = (array)$a;
        unset($a_arr['accion_sustantiva_id']);
        
        // Ensure we only insert columns that exist in actividades_sustantivas
        $act_arr = [
            'proyecto_id' => $a_arr['proyecto_id'],
            'numero' => $a_arr['numero'] ?? null,
            'descripcion' => $a_arr['descripcion'],
            'recursos_asociados' => $a_arr['recursos_asociados'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::connection('poa_prod')->table('actividades_sustantivas')->insert($act_arr);
    }
    
    // Delete from acciones_sustantivas
    if (count($acciones) > 0) {
        DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $py_2027_id)->delete();
        echo "Movidas " . count($acciones) . " actividades de acciones_sustantivas a actividades_sustantivas para el proyecto $py_2027_id.\n";
    } else {
        echo "No hay acciones para mover.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
