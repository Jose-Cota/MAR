<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('urg.nombre', 'LIKE', '%Recursos Materiales%')
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

if(!$proyecto) die("Proyecto no encontrado\n");

DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();
DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();

$actividades = [
    ['numero' => '1', 'descripcion' => 'Programa y procedimientos de adquisiciones'],
    ['numero' => '2', 'descripcion' => 'Servicios generales y contratación de servicios institucionales'],
    ['numero' => '3', 'descripcion' => 'Protección civil y capacitación de brigadas'],
    ['numero' => '4', 'descripcion' => 'Transparencia, solicitudes de información y atención de auditorías'],
];

foreach($actividades as $act) {
    DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
        'proyecto_id' => $proyecto->proyecto_id,
        'numero' => $act['numero'],
        'descripcion' => $act['descripcion'],
        'recursos_asociados' => '',
        'es_resumida' => 1
    ]);
    
    DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
        'proyecto_id' => $proyecto->proyecto_id,
        'numero' => $act['numero'],
        'descripcion' => $act['descripcion'],
        'recursos_asociados' => '',
        'es_resumida' => 1
    ]);
    echo "Insertada: {$act['descripcion']}\n";
}
echo "Done.\n";
