<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$alineaciones = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
    ->join('proyectos', 'pei_proyecto_alineaciones.proyecto_id', '=', 'proyectos.proyecto_id')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->where('unidades_responsables_gastos.ejercicio_id', 2) // Assuming 2027 is ej 2
    ->select('proyectos.proyecto_id', 'proyectos.nombre', 'pei_proyecto_alineaciones.pei_linea_estrategica_id', 'pei_proyecto_alineaciones.pei_objetivo_estrategico_id')
    ->get();

echo "Total alineaciones in 2027: " . $alineaciones->count() . "\n";
if ($alineaciones->count() > 0) {
    print_r($alineaciones->first());
}
