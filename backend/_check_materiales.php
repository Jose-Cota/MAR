<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$p = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('p.ejercicio_id', 19)
    ->where('p.nombre', 'like', '%materiales%')
    ->select('ro.nombre', 'p.proyecto_id')
    ->get();

foreach($p as $row) {
    echo "PROY: {$row->proyecto_id} | RO: {$row->nombre}\n";
}
