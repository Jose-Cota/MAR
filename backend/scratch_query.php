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
    ->select('proyecto_id', 'numero', 'nombre', 'responsable_operativo_id')
    ->get();

echo "Total proyectos 2027: " . count($proyectos) . "\n";
foreach ($proyectos as $p) {
    if ((int)$p->numero >= 15 && (int)$p->numero <= 25) {
        echo "ID: {$p->proyecto_id} | Num: {$p->numero} | RO: {$p->responsable_operativo_id} | Nom: {$p->nombre}\n";
    }
}
// Also print max number
$max = DB::connection('poa_prod')->table('proyectos')
    ->where('ejercicio_id', $ejercicio->ejercicio_id)
    ->max(DB::raw('CAST(numero AS UNSIGNED)'));
echo "Max numero: {$max}\n";
