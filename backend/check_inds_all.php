<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicio = 2026;

$proyectos = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
    ->where('ejercicios.ejercicio', $ejercicio)
    ->select('proyectos.proyecto_id', 'unidades_responsables_gastos.numero as urg', 'responsables_operativos.numero as ro', 'programas.numero as pg', 'subprogramas.numero as sp', 'proyectos.numero as py', 'proyectos.nombre')
    ->get();

$found = 0;
foreach ($proyectos as $py) {
    $count = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $py->proyecto_id)->count();
    if ($count > 0) {
        $clave = "{$py->urg}.{$py->ro}.{$py->pg}.{$py->sp}.{$py->py}";
        echo "- Clave: $clave | Total Indicadores: $count\n";
        $found++;
    }
}

if ($found === 0) {
    echo "NINGÚN proyecto en todo el ejercicio $ejercicio tiene indicadores.\n";
} else {
    echo "\nTotal de proyectos con indicadores: $found\n";
}
