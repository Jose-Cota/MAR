<?php
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== TODAS las áreas en BD ===\n";
$areas = DB::table('areas')->orderBy('area_id')->get();
foreach ($areas as $a) echo "  [{$a->area_id}] {$a->nombre}\n";

echo "\n=== TODOS los proyectos 2026 (ej=17) ===\n";
$p2026 = DB::table('proyectos')->where('ejercicio_id', 17)->select('proyecto_id','nombre')->get();
foreach ($p2026 as $p) echo "  [{$p->proyecto_id}] {$p->nombre}\n";

echo "\n=== TODOS los proyectos 2027 (ej=19) ===\n";
$p2027 = DB::table('proyectos')->where('ejercicio_id', 19)->select('proyecto_id','nombre')->get();
foreach ($p2027 as $p) echo "  [{$p->proyecto_id}] {$p->nombre}\n";

echo "\n=== Riesgos 2026 (ej=17) — area_id distintas ===\n";
$r2026 = DB::table('riesgos')->where('ejercicio_id',17)->select('area_id','local_id')->orderBy('area_id')->get();
$grouped = [];
foreach ($r2026 as $r) $grouped[$r->area_id][] = $r->local_id;
foreach ($grouped as $aId => $lids) {
    $aName = DB::table('areas')->where('area_id',$aId)->value('nombre') ?? '???';
    echo "  area_id=$aId ($aName): " . implode(', ',$lids) . "\n";
}

echo "\n=== Riesgos 2027 (ej=19) — area_id distintas ===\n";
$r2027 = DB::table('riesgos')->where('ejercicio_id',19)->select('area_id','local_id')->orderBy('area_id')->get();
$grouped2 = [];
foreach ($r2027 as $r) $grouped2[$r->area_id][] = $r->local_id;
foreach ($grouped2 as $aId => $lids) {
    $aName = DB::table('areas')->where('area_id',$aId)->value('nombre') ?? '???';
    echo "  area_id=$aId ($aName): " . implode(', ',$lids) . "\n";
}

echo "\n=== Responsables operativos 2026/2027 con sus unidades ===\n";
$ros = DB::table('responsables_operativos as ro')
    ->join('unidades_responsables_gastos as urg','urg.unidad_responsable_gasto_id','=','ro.unidad_responsable_gasto_id')
    ->whereIn('ro.ejercicio_id',[17,19])
    ->select('ro.responsable_operativo_id','ro.ejercicio_id','ro.nombre as ro_nombre','urg.nombre as urg_nombre','urg.clave')
    ->get();
foreach ($ros as $r) echo "  ro_id={$r->responsable_operativo_id} ej={$r->ejercicio_id} | {$r->urg_nombre} ({$r->clave}) | {$r->ro_nombre}\n";
