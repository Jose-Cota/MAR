<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$acciones = DB::table('acciones_sustantivas')
    ->join('proyectos', 'acciones_sustantivas.proyecto_id', '=', 'proyectos.proyecto_id')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->where('proyectos.ejercicio_id', 17)
    ->where('unidades_responsables_gastos.nombre', 'LIKE', '%Contralor%')
    ->select('acciones_sustantivas.*', 'unidades_responsables_gastos.nombre as urg')
    ->get();

foreach ($acciones as $acc) {
    echo "Accion {$acc->numero}: {$acc->descripcion} (URG: {$acc->urg}, ID: {$acc->accion_sustantiva_id})\n";
}
