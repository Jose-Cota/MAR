<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 17)
    ->where('responsables_operativos.unidad_responsable_gasto_id', 2)
    ->select('proyectos.proyecto_id')
    ->pluck('proyecto_id');

echo "Proyectos for area 2: " . implode(', ', $proyectos->toArray()) . "\n";

if ($proyectos->isNotEmpty()) {
    $actividades = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos)->count();
    $acciones = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectos)->count();

    echo "Count in actividades_sustantivas: $actividades\n";
    echo "Count in acciones_sustantivas: $acciones\n";
}
