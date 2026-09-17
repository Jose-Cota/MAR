<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
$proyectos = DB::connection('poa_prod')->table('proyectos')
    ->where('ejercicio_id', $ejercicio->ejercicio_id)
    ->orderBy(DB::raw('CAST(numero AS UNSIGNED)'))
    ->select('proyecto_id', 'numero', 'nombre')
    ->get();

foreach ($proyectos as $p) {
    echo "{$p->numero} - {$p->proyecto_id} | ";
}
echo "\n";
