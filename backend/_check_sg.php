<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyPrincipal = 1768; // SG 2027
$acts = DB::table('acciones_sustantivas')->where('proyecto_id', $proyPrincipal)->get();
echo "SG 2027 tiene " . count($acts) . " actividades:\n";
foreach($acts as $a) {
    echo "- Numero: {$a->numero} | ID: {$a->accion_sustantiva_id} | {$a->descripcion}\n";
}
