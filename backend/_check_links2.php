<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== actividad_riesgo: totales y rangos ===\n";
echo "filas=" . DB::table('actividad_riesgo')->count() . "\n";
echo "riesgo_ids distintos (" . DB::table('actividad_riesgo')->distinct()->count('riesgo_id') . "):\n";
foreach (DB::table('actividad_riesgo')->distinct()->get(['riesgo_id']) as $r) echo $r->riesgo_id . ", ";
echo "\n\n=== riesgos con esos ids ===\n";
$ids = DB::table('actividad_riesgo')->distinct()->pluck('riesgo_id');
foreach ($ids as $rid) {
    $rz = DB::table('riesgos')->where('id', $rid)->first();
    echo "riesgo_id=$rid -> " . ($rz ? "ej={$rz->ejercicio_id} area={$rz->area_id} local={$rz->local_id} " . mb_substr($rz->riesgo, 0, 40) : "NO EXISTE en riesgos") . "\n";
}
echo "\n=== conteo por link ===\n";
foreach ($ids as $rid) {
    echo "riesgo_id=$rid links=" . DB::table('actividad_riesgo')->where('riesgo_id', $rid)->count() . "\n";
}
echo "\n=== riesgos sin ningun link (todas las ejercicios) ===\n";
$rs = DB::table('riesgos')->get();
$sin = [];
foreach ($rs as $r) {
    $n = DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->count();
    if ($n === 0) $sin[] = "id={$r->id} ej={$r->ejercicio_id} area={$r->area_id} local={$r->local_id}";
}
echo "total riesgos=" . count($rs) . ", sin vinculo=" . count($sin) . "\n";
foreach ($sin as $s) echo "  $s\n";