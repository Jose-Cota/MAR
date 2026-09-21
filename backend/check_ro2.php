<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check responsables_operativos table structure
$ro = DB::table('responsables_operativos')->first();
echo "First RO: " . json_encode($ro) . "\n\n";

// Check what the urg_id 564 in responsables_operativos actually is
$ro564 = DB::table('responsables_operativos')->where('unidad_responsable_gasto_id', 564)->first();
echo "RO with URG 564: " . json_encode($ro564) . "\n\n";

// Maybe unidad_responsable_gasto_id in responsables_operativos links to a different table?
// Check the distinct values
$urgIds = DB::table('responsables_operativos')
    ->join('proyectos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 19)
    ->select(DB::raw('DISTINCT responsables_operativos.unidad_responsable_gasto_id'))
    ->orderBy('responsables_operativos.unidad_responsable_gasto_id')
    ->get();

echo "Distinct URG IDs from proyectos 2027:\n";
print_r($urgIds->pluck('unidad_responsable_gasto_id')->toArray());
