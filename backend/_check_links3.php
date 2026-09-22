<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$rs = DB::table('riesgos')->get();
$con = 0; $sin = 0;
foreach ($rs as $r) {
    $n = DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->count();
    if ($n > 0) $con++; else $sin++;
}
echo "RIESGOS (toda la tabla): total=" . count($rs) . "  con>=1 vinculo=$con  sin vinculo=$sin\n";

// distinct riesgo ids by ejercicio value
$porEj = $rs->groupBy('ejercicio_id');
echo "\nRiesgos por valor de ejercicio_id:\n";
foreach ($porEj as $k => $g) {
    $c = 0; $s = 0; $total = $g->count();
    foreach ($g as $r) {
        $n = DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->count();
        if ($n > 0) $c++; else $s++;
    }
    echo "  ejercicio_id=$k : total=$total | con=$c sin=$s\n";
}

echo "\nACTIVIDADES con/sin vinculación (todas las tablas acciones/actividades):\n";
foreach (['actividades_sustantivas', 'acciones_sustantivas'] as $t) {
    $tot = 0; $c = 0; $s = 0;
    foreach (DB::table($t)->get() as $a) {
        $tot++;
        $aid = $a->id ?? $a->accion_sustantiva_id ?? null;
        $n = $aid ? DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $aid)->count() : 0;
        if ($n > 0) $c++; else $s++;
    }
    echo "  $t: total=$tot con=$c sin=$s\n";
}

echo "\nactividad_riesgo: links=" . DB::table('actividad_riesgo')->count() . "\n";
echo "riesgos distintos en pivot=" . DB::table('actividad_riesgo')->distinct()->count('riesgo_id') . "\n";
echo "actividades distintas en pivot=" . DB::table('actividad_riesgo')->distinct()->count('actividad_sustantiva_id') . "\n";