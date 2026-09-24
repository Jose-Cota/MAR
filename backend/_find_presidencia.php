<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::table('unidades_responsables_gastos')->where('nombre', 'LIKE', '%Presidencia%')->where('ejercicio_id', 2027)->first();
if(!$urg) {
    // Try by joining ejercicios
    $urg = DB::table('unidades_responsables_gastos as urg')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('urg.nombre', 'LIKE', '%Presidencia%')
        ->where('ej.ejercicio', 2027)
        ->select('urg.*')
        ->first();
}

if(!$urg) die("URG Presidencia no encontrada\n");

echo "URG: " . $urg->nombre . " (" . $urg->numero . ")\n";

$proyecto = DB::table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
    ->select('p.*')
    ->first();

if(!$proyecto) die("Proyecto no encontrado\n");

echo "Proyecto ID: " . $proyecto->proyecto_id . "\n";

$riesgos = DB::table('riesgos')->where('proyecto_id', $proyecto->proyecto_id)->get();
echo "Riesgos encontrados: " . count($riesgos) . "\n";
foreach($riesgos as $r) {
    echo " - " . $r->numero . ": " . $r->riesgo_id . "\n";
}
