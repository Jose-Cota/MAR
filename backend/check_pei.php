<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sp = DB::connection('poa_prod')->table('subprogramas')->where('numero', '07')->first();
if (!$sp) {
    echo "SP 07 not found\n";
    return;
}

$mappings = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->where('subprograma_id', $sp->subprograma_id)->get();
echo "Mappings for SP 07:\n";
print_r($mappings);

// Check if any project from 2027 in SP 07 has an alignment
$ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
$pys = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->where('unidades_responsables_gastos.ejercicio_id', $ej2027->ejercicio_id)
    ->where('proyectos.numero', '08')
    ->select('proyectos.*')
    ->get();

echo "\nProyectos 08 in 2027:\n";
foreach ($pys as $py) {
    echo "PY: " . $py->proyecto_id . "\n";
    $align = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $py->proyecto_id)->first();
    echo "Align:\n";
    print_r($align);
}
