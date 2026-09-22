<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1) Encontrar URG
$urgs = DB::table('unidades_responsables_gastos')->where('nombre', 'like', '%Procedimientos Sancionadores%')->get();
foreach ($urgs as $u) echo "URG id={$u->unidad_responsable_gasto_id} ej={$u->ejercicio_id} num={$u->numero} nombre={$u->nombre}\n";
if ($urgs->isEmpty()) { echo "no matching URG\n"; return; }
$urg = $urgs->first();
$urgId = $urg->unidad_responsable_gasto_id;

// 2) Ejercicio 2027 -> db id
$ejRow = DB::table('ejercicios')->where('ejercicio', 2027)->first();
$ejid = $ejRow->ejercicio_id;

// 3) ROs del ejercicio con esa URG
$ros = DB::table('responsables_operativos')->where('ejercicio_id', $ejid)->where('unidad_responsable_gasto_id', $urgId)->get();
echo "\nROs:\n";
foreach ($ros as $ro) echo "  ro_id={$ro->responsable_operativo_id} nombre={$ro->nombre}\n";

// 4) Proyectos de esos ROs
$roIds = $ros->pluck('responsable_operativo_id')->toArray();
$proy = DB::table('proyectos')->whereIn('responsable_operativo_id', $roIds)->where('ejercicio_id', $ejid)->get();
echo "\nProyectos (ejercicio_id=$ejid):\n";
foreach ($proy as $p) echo "  proy={$p->proyecto_id} n={$p->numero} nombre=".substr($p->nombre,0,60)."\n";

// 5) Simular getFichas: actividades nuevas y viejas de cada proyecto + pivots
foreach ($proy as $p) {
    echo "\n== Proyecto {$p->proyecto_id} ==\n";
    $nuevas = DB::table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    echo "  nuevas=".$nuevas->count();
    $viejas = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
    echo " viejas=".$viejas->count()."\n";
    // rows used by getFichas (nuevas, o si vacias, viejas)
    $usadas = $nuevas->count() ? $nuevas : $viejas;
    foreach ($usadas as $a) {
        $actId = $a->id ?? $a->accion_sustantiva_id;
        $riesgos = DB::table('actividad_riesgo')->join('riesgos','actividad_riesgo.riesgo_id','=','riesgos.id')
            ->where('actividad_riesgo.actividad_sustantiva_id', $actId)
            ->select('riesgos.local_id','riesgos.probabilidad','riesgos.impacto')->get();
        $marks = $riesgos->pluck('local_id')->implode(',');
        echo "    actId=$actId (".(isset($a->id)?'NEW':'OLD').") num={$a->numero} desc=".substr($a->descripcion,0,55)." -> [$marks]\n";
    }
}

// 6) Riesgos del área
echo "\nRiesgos area=$urgId ejercicio=$ejid:\n";
foreach (DB::table('riesgos')->where('area_id', $urgId)->where('ejercicio_id', $ejid)->get() as $r) {
    $pivots = DB::table('actividad_riesgo')->where('riesgo_id',$r->id)->get();
    $detalles = [];
    foreach ($pivots as $pv) {
        $n = DB::table('actividades_sustantivas')->where('id',$pv->actividad_sustantiva_id)->first();
        $o = DB::table('acciones_sustantivas')->where('accion_sustantiva_id',$pv->actividad_sustantiva_id)->first();
        $proyId = $n->proyecto_id ?? $o->proyecto_id ?? '?';
        $origen = $n ? 'NEW' : ($o ? 'OLD' : '??');
        $desc = substr(($n->descripcion ?? $o->descripcion ?? '?'), 0, 50);
        $detalles[] = "pv={$pv->actividad_sustantiva_id}[$origen proy=$proyId] $desc";
    }
    echo "  id={$r->id} local={$r->local_id} -> ".implode(" | ", $detalles)."\n";
}