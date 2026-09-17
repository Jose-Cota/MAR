<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total2026 = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
    ->join('proyectos', 'pei_proyecto_alineaciones.proyecto_id', '=', 'proyectos.proyecto_id')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->where('ejercicios.ejercicio', 2026)
    ->count();

echo "Total PEI alignments in 2026: {$total2026}\n";

if ($total2026 > 0) {
    $example = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
        ->join('proyectos', 'pei_proyecto_alineaciones.proyecto_id', '=', 'proyectos.proyecto_id')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
        ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
        ->where('ejercicios.ejercicio', 2026)
        ->select('proyectos.nombre', 'unidades_responsables_gastos.numero as urg', 'proyectos.numero as py')
        ->first();
    echo "Example project with PEI in 2026: URG {$example->urg}, PY {$example->py} - {$example->nombre}\n";
}
