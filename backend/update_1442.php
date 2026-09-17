<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyecto_id = 1442;

$proyecto = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', $proyecto_id)->first();

if ($proyecto) {
    echo "Proyecto encontrado:\n";
    echo "ID: {$proyecto->proyecto_id} | Numero: {$proyecto->numero} | Nombre: {$proyecto->nombre} | Estado actual: {$proyecto->status}\n";
    
    DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', $proyecto_id)->update([
        'status' => 'Captura',
        'fecha_verificacion' => null
    ]);
    
    echo "=> Estatus actualizado a 'Captura' exitosamente.\n";
} else {
    echo "No se encontró el proyecto con ID 1442.\n";
}
