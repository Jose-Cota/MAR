<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('urg.nombre', 'LIKE', '%Secretaría Administrativa%')
    ->where('ej.ejercicio', 2027)
    ->select('urg.*')
    ->first();

if(!$urg) die("URG no encontrada\n");
echo "URG: " . $urg->nombre . "\n";

$proyecto = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
    ->select('p.*')
    ->first();

echo "Proyecto ID: " . $proyecto->proyecto_id . "\n";

$acts = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->get();
echo "Actividades_sustantivas en DB:\n";
foreach($acts as $a) {
    echo "- [{$a->id}] {$a->descripcion} (es_resumida: {$a->es_resumida})\n";
}

$acciones = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->get();
echo "Acciones_sustantivas en DB:\n";
foreach($acciones as $a) {
    echo "- [{$a->accion_sustantiva_id}] {$a->descripcion} (es_resumida: {$a->es_resumida})\n";
}
