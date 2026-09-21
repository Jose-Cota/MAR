<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ejercicio_db_id = 17;
    $area_id = 2;

    $query = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', $ejercicio_db_id)
        ->select('proyectos.*', 'proyectos.proyecto_id as id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id');

    if ($area_id !== 'todas') {
        $query->where('responsables_operativos.unidad_responsable_gasto_id', $area_id);
    }

    $proyectos = $query->get();
    echo "Proyectos count: " . count($proyectos) . "\n";

    foreach ($proyectos as $p) {
        $p->acciones = DB::table('acciones_sustantivas')
            ->where('proyecto_id', $p->id)
            ->get();
            
        echo "Proyecto {$p->id} acciones count: " . count($p->acciones) . "\n";

        foreach ($p->acciones as $accion) {
            $accion->riesgos_vinculados = DB::table('actividad_riesgo')
                ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
                ->where('actividad_riesgo.actividad_sustantiva_id', $accion->accion_sustantiva_id)
                ->select('riesgos.id', 'riesgos.local_id', 'riesgos.riesgo')
                ->get();
        }
    }
    
    echo "Success!\n";

} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
