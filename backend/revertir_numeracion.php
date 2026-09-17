<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::connection('poa_prod')->beginTransaction();

    $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
    if (!$ejercicio) {
        die("Ejercicio 2027 no encontrado\n");
    }

    $proyectos = DB::connection('poa_prod')->table('proyectos')
        ->where('ejercicio_id', $ejercicio->ejercicio_id)
        ->where(DB::raw('CAST(numero AS UNSIGNED)'), '>=', 18)
        ->orderBy(DB::raw('CAST(numero AS UNSIGNED)'), 'desc') // Orden inverso para evitar colisiones
        ->select('proyecto_id', 'numero', 'nombre')
        ->get();

    echo "Proyectos a modificar: " . count($proyectos) . "\n";

    foreach ($proyectos as $p) {
        $old_numero = (int)$p->numero;
        $new_numero = $old_numero + 2;
        
        $new_numero_str = str_pad((string)$new_numero, 2, '0', STR_PAD_LEFT);
        
        echo "Actualizando ID {$p->proyecto_id}: {$p->numero} -> {$new_numero_str} ({$p->nombre})\n";
        
        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $p->proyecto_id)
            ->update(['numero' => $new_numero_str]);
    }

    DB::connection('poa_prod')->commit();
    echo "\n¡Numeración revertida con éxito!\n";
    
    // Verificación
    $verificacion = DB::connection('poa_prod')->table('proyectos')
        ->where('ejercicio_id', $ejercicio->ejercicio_id)
        ->where(DB::raw('CAST(numero AS UNSIGNED)'), '>=', 18)
        ->orderBy(DB::raw('CAST(numero AS UNSIGNED)'), 'asc')
        ->select('proyecto_id', 'numero')
        ->get();
        
    echo "\nLista actual de números desde el 18:\n";
    $numeros = [];
    foreach ($verificacion as $v) {
        $numeros[] = $v->numero;
    }
    echo implode(", ", $numeros) . "\n";
    
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Ocurrió un error: " . $e->getMessage() . "\n";
}
