<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 17)
    ->select('proyectos.proyecto_id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id')
    ->get();

$urg_counts = [];
foreach ($proyectos as $p) {
    $urg_counts[$p->urg_id] = ($urg_counts[$p->urg_id] ?? 0) + 1;
}
echo "URG counts for Ejercicio 17 projects:\n";
print_r($urg_counts);
