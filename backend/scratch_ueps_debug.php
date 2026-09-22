<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "== Riesgos con area (20 o UEPS) ejercicio 19 ==\n";
$rows = DB::table('riesgos')->whereIn('area_id', [20, 'UEPS'])->where('ejercicio_id', 19)->orderBy('area_id')->orderBy('local_id')->get();
foreach ($rows as $r) {
    $pivots = DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->pluck('actividad_sustantiva_id')->toArray();
    echo "  id={$r->id} area={$r->area_id} local={$r->local_id} pivots=[".implode(',', $pivots)."]\n";
}

echo "\n== Pivots hacia actividades 414,415,416,519 ==\n";
foreach ([414, 415, 416, 519] as $actId) {
    $links = DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $actId)->get();
    foreach ($links as $l) {
        $r = DB::table('riesgos')->where('id', $l->riesgo_id)->first();
        echo "  act=$actId -> riesgo_id={$l->riesgo_id} local={$r->local_id} area={$r->area_id}\n";
    }
    if ($links->isEmpty()) echo "  act=$actId -> (sin vínculo)\n";
}

echo "\n== Todas las actividades nuevas del proyecto 1461 ==\n";
foreach (DB::table('actividades_sustantivas')->where('proyecto_id', 1461)->get() as $a) {
    echo "  id={$a->id} num={$a->numero} {$a->descripcion}\n";
}

echo "\n== total riesgos por área tipo (2027) ==\n";
foreach (DB::table('riesgos')->where('ejercicio_id', 19)->select('area_id', DB::raw('count(*) as n'))->groupBy('area_id')->get() as $g) echo "  area={$g->area_id} n={$g->n}\n";