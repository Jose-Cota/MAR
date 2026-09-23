<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find CI (Contraloría Interna) URG
$urg = DB::table('unidades_responsables_gastos')->where('nombre', 'LIKE', '%Contralor%')->first();
if (!$urg) {
    die("URG not found\n");
}
echo "URG: {$urg->nombre} (ID: {$urg->unidad_responsable_gasto_id})\n";

// Get actions for CI 2026
$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('responsables_operativos.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
    ->where('proyectos.ejercicio_id', 17) // 17 is 2026
    ->pluck('proyectos.proyecto_id')
    ->toArray();

echo "Proyectos: " . implode(', ', $proyectos) . "\n";

$acciones = DB::table('acciones_sustantivas')
    ->whereIn('proyecto_id', $proyectos)
    ->get();

foreach ($acciones as $acc) {
    echo "Accion {$acc->numero}: {$acc->descripcion} (ID: {$acc->accion_sustantiva_id})\n";
    $links = DB::table('actividad_riesgo')
        ->join('riesgos', 'riesgos.id', '=', 'actividad_riesgo.riesgo_id')
        ->where('actividad_riesgo.actividad_sustantiva_id', $acc->accion_sustantiva_id)
        ->select('riesgos.local_id', 'riesgos.ejercicio_id')
        ->get();
    
    $linked = [];
    foreach ($links as $l) {
        $linked[] = $l->local_id;
    }
    echo "  Riesgos: " . implode(', ', $linked) . "\n";
}
