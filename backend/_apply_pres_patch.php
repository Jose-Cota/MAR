<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('urg.nombre', 'LIKE', '%Presidencia%')
    ->where('ej.ejercicio', 2027)
    ->select('urg.*')
    ->first();

if(!$urg) die("URG Presidencia no encontrada\n");
echo "URG: " . $urg->nombre . "\n";

$proyecto = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
    ->select('p.*')
    ->first();

if(!$proyecto) die("Proyecto no encontrado\n");

$riesgosDb = DB::connection('poa_prod')->table('riesgos')->where('area_id', 'PRES')->where('ejercicio_id', 2027)->get();
if(count($riesgosDb) == 0) {
    // maybe ejercicio_id is 1 or 2
    $riesgosDb = DB::connection('poa_prod')->table('riesgos')->where('area_id', 'PRES')->where('ejercicio_id', $urg->ejercicio_id)->get();
}
echo "Riesgos encontrados: " . count($riesgosDb) . "\n";

$actividades = [
    ['numero' => '1', 'descripcion' => 'Representación oficial e institucional del Tribunal', 'riesgo' => 'R3'],
    ['numero' => '2', 'descripcion' => 'Reuniones de trabajo y coordinación institucional', 'riesgo' => 'R2'],
    ['numero' => '3', 'descripcion' => 'Seguimiento al cumplimiento de acuerdos y resoluciones del Pleno', 'riesgo' => 'R1'],
];

foreach($actividades as $act) {
    $id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
        'proyecto_id' => $proyecto->proyecto_id,
        'numero' => $act['numero'],
        'descripcion' => $act['descripcion'],
        'recursos_asociados' => '',
        'es_resumida' => 1
    ]);
    
    echo "Insertada actividad {$act['numero']}: {$act['descripcion']} (ID: $id)\n";

    $riesgoAsignado = $riesgosDb->where('local_id', 'PRES-2027-' . $act['riesgo'])->first();
    if(!$riesgoAsignado) $riesgoAsignado = $riesgosDb->where('local_id', $act['riesgo'])->first();
    if(!$riesgoAsignado) $riesgoAsignado = $riesgosDb->filter(function($r) use ($act) { return strpos($r->local_id, $act['riesgo']) !== false; })->first();

    if($riesgoAsignado) {
        DB::connection('poa_prod')->table('actividad_riesgo')->insert([
            'actividad_sustantiva_id' => $id,
            'riesgo_id' => $riesgoAsignado->id
        ]);
        echo " -> Ligada a Riesgo: " . $riesgoAsignado->local_id . "\n";
    } else {
        echo " -> Riesgo no encontrado para: " . $act['riesgo'] . "\n";
    }
}
echo "Done.\n";
