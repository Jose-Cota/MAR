<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')->where('ejercicio_id', 19)->get();
echo "Proyectos en 2027: " . $proyectos->count() . "\n";
foreach($proyectos as $p) {
    echo "Proyecto: {$p->proyecto_id} - RO_ID: {$p->responsable_operativo_id}\n";
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    if ($ro) {
        echo "  -> UR_ID: {$ro->unidad_responsable_gasto_id}\n";
        $ur = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $ro->unidad_responsable_gasto_id)->first();
        if ($ur) {
            echo "    -> UR: {$ur->nombre}\n";
        }
    }
}
