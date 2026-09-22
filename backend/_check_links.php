<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
foreach ([17, 19] as $ej) {
    $rs = DB::table('riesgos')->where('ejercicio_id', $ej)->get();
    $con = 0; $sin = [];
    foreach ($rs as $r) {
        $n = DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->count();
        if ($n > 0) $con++; else $sin[] = 'UR' . ($r->area_id) . '/' . ($r->local_id);
    }
    echo "EJ $ej: riesgos=" . count($rs) . " con>=1 vinculo=$con sin vinculo=" . count($sin) . PHP_EOL;
    echo "  sin: " . implode(', ', $sin) . PHP_EOL;
}
// count acciones without any riesgo
foreach ([17, 19] as $ej) {
    $proyectos = DB::table('proyectos')->where('ejercicio_id', $ej)->pluck('proyecto_id');
    $sinR = 0; $conR = 0; $total = 0;
    foreach ($proyectos as $pid) {
        $acts = DB::table('actividades_sustantivas')->where('proyecto_id', $pid)->get();
        if ($acts->isEmpty()) $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $pid)->get();
        foreach ($acts as $a) {
            $total++;
            $aid = $a->id ?? $a->accion_sustantiva_id ?? null;
            $n = $aid ? DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $aid)->count() : 0;
            if ($n > 0) $conR++; else $sinR++;
        }
    }
    echo "EJ $ej: actividades totales=$total con riesgos=$conR sin riesgos=$sinR" . PHP_EOL;
}