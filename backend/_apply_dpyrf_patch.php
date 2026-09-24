<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Buscar URG DPyRF en 2027
$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->where('urg.nombre', 'LIKE', '%Planeación y Recursos Financieros%')
    ->where('ej.ejercicio', 2027)
    ->select('urg.*')
    ->first();

if(!$urg) die("URG DPyRF no encontrada\n");
echo "URG: " . $urg->nombre . "\n";

$proyecto = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
    ->select('p.*')
    ->first();

if(!$proyecto) die("Proyecto no encontrado\n");
echo "Proyecto ID: " . $proyecto->proyecto_id . "\n";

// Borrar actividad_riesgo
$acts = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->pluck('id');
if(count($acts) > 0) {
    DB::connection('poa_prod')->table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $acts)->delete();
}
// acts2 deleted

// Borrar actividades viejas
DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();
DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();
echo "Actividades viejas borradas.\n";

$actividades = [
    ['numero' => '1', 'descripcion' => 'Planeación, integración del POA y Anteproyecto de Presupuesto'],
    ['numero' => '2', 'descripcion' => 'Informes programático-presupuestales, financieros y Cuenta Pública'],
    ['numero' => '3', 'descripcion' => 'Gestión, control y seguimiento presupuestal'],
    ['numero' => '4', 'descripcion' => 'Registro, control y armonización contable'],
    ['numero' => '5', 'descripcion' => 'Conciliaciones y control de recursos financieros'],
];

foreach($actividades as $act) {
    $id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
        'proyecto_id' => $proyecto->proyecto_id,
        'numero' => $act['numero'],
        'descripcion' => $act['descripcion'],
        'recursos_asociados' => '',
        'es_resumida' => 1
    ]);
    
    $accionId = DB::connection('poa_prod')->table('acciones_sustantivas')->insertGetId([
        'proyecto_id' => $proyecto->proyecto_id,
        'numero' => $act['numero'],
        'descripcion' => $act['descripcion'],
        'recursos_asociados' => '',
        'es_resumida' => 1
    ]);

    echo "Insertada actividad {$act['numero']}: {$act['descripcion']} (ID: $id)\n";
}

echo "Done.\n";
