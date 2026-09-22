<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== Riesgos de UR 11 (Instituto de Formación y Capacitación) por todas las poblaciones ===\n";
$rs = DB::table('riesgos')->where('area_id', 11)->orWhere('area_id', 'IFyC')->orderBy('id')->get();
foreach ($rs as $r) {
    echo sprintf("id=%-4d | area_id=%-6s | ejercicio_id=%-6s | local_id=%-18s | vea = %s\n",
        $r->id, var_export($r->area_id, true), var_export($r->ejercicio_id, true), $r->local_id,
        mb_substr($r->riesgo, 0, 45));
}

echo "\n=== Cualquier riesgo con local_id R1 o R5 y area numerica 11 ===\n";
$rs2 = DB::table('riesgos')->whereIn('local_id', ['R1', 'R5'])->where('area_id', 11)->get();
foreach ($rs2 as $r) {
    echo "id=$r->id area_id=$r->area_id ejercicio_id=$r->ejercicio_id local=$r->local_id status=$r->status | " . mb_substr($r->riesgo, 0, 50) . "\n";
}

echo "\n=== Fila modificada recientemente (updated_at) con ur 11 ===\n";
$recientes = DB::table('riesgos')->where('area_id', 11)->orWhere('area_id', 'IFyC')->orderByDesc('updated_at')->take(8)->get();
foreach ($recientes as $r) {
    echo "id=$r->id area=$r->area_id ej=$r->ejercicio_id local=$r->local_id upd=" . $r->updated_at . "\n";
}