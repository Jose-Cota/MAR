<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->where('p.ejercicio_id', 19)
    ->where('ro.nombre', 'like', '%Armando Ambriz%')
    ->select('p.proyecto_id', 'p.nombre as p_nombre')
    ->get();

foreach ($proyectos as $p) {
    echo "PROYECTO: {$p->proyecto_id} | {$p->p_nombre}\n";
    $acts = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    foreach ($acts as $a) {
        echo "  ACT: {$a->numero} | {$a->descripcion}\n";
    }
}
