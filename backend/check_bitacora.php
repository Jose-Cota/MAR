<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $py_2027_id = 1421;
    
    if (Schema::connection('poa_prod')->hasTable('proyecto_bitacoras')) {
        $bitacora = DB::connection('poa_prod')->table('proyecto_bitacoras')
            ->where('proyecto_id', $py_2027_id)
            ->orderBy('fecha', 'desc')
            ->get();
            
        echo "=== BITACORA PARA PROYECTO $py_2027_id ===\n";
        foreach ($bitacora as $b) {
            echo "Fecha: {$b->fecha} | Usuario: {$b->usuario_id} | Accion: {$b->accion}\n";
            if (!empty($b->detalles)) echo "Detalles: {$b->detalles}\n";
        }
    } else {
        echo "Tabla proyecto_bitacoras no existe.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
