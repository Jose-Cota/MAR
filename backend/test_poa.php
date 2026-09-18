<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ejercicio_id = 2026;
$area_id = 240;

$ejercicioRow = DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
$ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;

echo "ejercicio_db_id: $ejercicio_db_id\n";

$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', $ejercicio_db_id)
    ->where('responsables_operativos.unidad_responsable_gasto_id', $area_id)
    ->select('proyectos.*', 'proyectos.proyecto_id as id')
    ->get();

echo "Proyectos encontrados: " . count($proyectos) . "\n";

foreach ($proyectos as $p) {
    $p->metas = DB::table('metas')->where('proyecto_id', $p->id)->get();
    $p->indicadores = DB::table('indicadores')->where('proyecto_id', $p->id)->get();
    $p->acciones = DB::table('acciones_sustantivas')->where('proyecto_id', $p->id)->get();
    foreach ($p->acciones as $accion) {
        $accion->riesgos_vinculados = DB::table('actividad_riesgo')
            ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
            ->where('actividad_riesgo.actividad_sustantiva_id', $accion->accion_sustantiva_id)
            ->select('riesgos.id', 'riesgos.local_id', 'riesgos.riesgo')
            ->get();
    }
    echo "  - {$p->nombre} | metas: " . count($p->metas) . " | acciones: " . count($p->acciones) . "\n";
}
echo "\nOK\n";
