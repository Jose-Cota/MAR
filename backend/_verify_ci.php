<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Simulate POAFichasController for area_id 7 (Contraloría)
$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 17)
    ->where('responsables_operativos.unidad_responsable_gasto_id', 244)
    ->select('proyectos.*', 'proyectos.proyecto_id as id')
    ->get();

foreach ($proyectos as $p) {
    echo "Proyecto: {$p->nombre} (ID: {$p->id})\n";
    $acciones = DB::table('acciones_sustantivas')->where('proyecto_id', $p->id)->orderBy('numero')->get();
    foreach ($acciones as $a) {
        $riesgos = DB::table('actividad_riesgo')
            ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
            ->where('actividad_riesgo.actividad_sustantiva_id', $a->accion_sustantiva_id)
            ->select('riesgos.local_id')
            ->get();
        $riesgoList = implode(', ', $riesgos->pluck('local_id')->toArray());
        echo "  - {$a->numero} | {$a->descripcion} | Riesgos: {$riesgoList}\n";
    }
}
