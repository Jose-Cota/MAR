<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// ¿Qué campos tiene responsables_operativos?
$ro = DB::table('responsables_operativos')->where('ejercicio_id', 19)->first();
echo "Campos RO: " . json_encode($ro) . "\n\n";

// ROs del ejercicio 19
$ros = DB::table('responsables_operativos')->where('ejercicio_id', 19)->get();
echo "Total ROs ej19: " . count($ros) . "\n";

// ¿Cuántos ROs distintos hay por unidad_responsable_gasto_id?
$byUrg = [];
foreach ($ros as $r) {
    $byUrg[$r->unidad_responsable_gasto_id] = ($byUrg[$r->unidad_responsable_gasto_id] ?? 0) + 1;
}
echo "ROs por URG:\n";
arsort($byUrg);
foreach ($byUrg as $urgId => $cnt) {
    echo "  urg_id=$urgId -> $cnt ROs\n";
}

// Ver los ROs del urg_id 564 (el que corresponde a 2027)
echo "\nROs con urg_id=564:\n";
$r564 = DB::table('responsables_operativos')->where('unidad_responsable_gasto_id', 564)->get();
foreach ($r564 as $r) {
    echo "  ro_id={$r->responsable_operativo_id}, nombre={$r->nombre}\n";
}

// ¿Proyectos de ej19 agrupados por responsable_operativo?
echo "\nProyectos de ej19 por ro_id:\n";
$pbyRo = DB::table('proyectos')
    ->where('ejercicio_id', 19)
    ->select('responsable_operativo_id', DB::raw('COUNT(*) as cnt'))
    ->groupBy('responsable_operativo_id')
    ->get();
foreach ($pbyRo as $pr) {
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $pr->responsable_operativo_id)->first();
    echo "  ro_id={$pr->responsable_operativo_id}, urg_id=" . ($ro->unidad_responsable_gasto_id ?? '?') . ", urg_nombre=" . ($ro->nombre ?? '?') . ", proyectos={$pr->cnt}\n";
}
