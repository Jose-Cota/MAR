<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$ps = DB::table('proyectos')->where('ejercicio_id', 19)->where('responsable_operativo_id', 965)->get();
foreach($ps as $p) {
    echo "Proyecto {$p->proyecto_id}:\n";
    $accs = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    foreach($accs as $a) {
        echo "  Accion {$a->accion_sustantiva_id}: {$a->descripcion}\n";
        $rs = DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $a->accion_sustantiva_id)->get();
        foreach($rs as $r) {
            echo "    -> Riesgo ID: {$r->riesgo_id}\n";
        }
    }
}
