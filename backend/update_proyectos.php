<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicio2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();

if (!$ejercicio2027) {
    echo "No se encontró ejercicio 2027.\n";
} else {
    echo "Ejercicio 2027 ID: {$ejercicio2027->ejercicio_id}\n";
    $proyectos = DB::connection('poa_prod')
        ->table('proyectos')
        ->where('ejercicio_id', $ejercicio2027->ejercicio_id)
        ->get();
        
    foreach ($proyectos as $p) {
        echo "ID: {$p->proyecto_id} | Num: {$p->numero} | Nombre: {$p->nombre} | Status: {$p->status}\n";
    }
}
