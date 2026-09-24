<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proys = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('urg.ejercicio_id', 19)
    ->select('p.proyecto_id', 'p.ejercicio_id as p_ej', 'urg.nombre')
    ->get();

foreach($proys as $p) {
    echo "Proy: {$p->proyecto_id} | Ejercicio: {$p->p_ej} | {$p->nombre}\n";
}
