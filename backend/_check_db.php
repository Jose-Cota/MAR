<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$projs = DB::table('proyectos')
    ->where('ejercicio_id', 17)
    ->whereIn('responsable_operativo_id', [446, 459])
    ->get();

foreach ($projs as $p) {
    echo "PROJ: {$p->proyecto_id} | Name: {$p->nombre} | RO: {$p->responsable_operativo_id}\n";
    $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    foreach ($acts as $a) {
        echo "  - ACT: {$a->accion_sustantiva_id} | Desc: {$a->descripcion}\n";
        $risks = DB::table('actividad_riesgo')
            ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
            ->where('actividad_sustantiva_id', $a->accion_sustantiva_id)
            ->pluck('local_id')->toArray();
        echo "    Risks: " . implode(', ', $risks) . "\n";
    }
}
