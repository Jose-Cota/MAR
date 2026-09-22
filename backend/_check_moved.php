<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== Riesgos numeric-UR con ejercicio año (moved by bad save) ===\n";
$moved = DB::table('riesgos')->whereIn('ejercicio_id', [2026, 2027])->whereRaw('area_id NOT LIKE "%-%" AND area_id REGEXP "^[0-9]+$"')->orderBy('area_id')->get();
foreach ($moved as $r) {
    echo "id=$r->id area=$r->area_id ej=$r->ejercicio_id local=" . $r->local_id . " upd=" . $r->updated_at . "\n";
}
if ($moved->isEmpty()) echo "(ninguno)\n";

echo "\n=== último increase of every ejercicio_id value ===\n";
foreach (DB::table('riesgos')->selectRaw('ejercicio_id, count(*) n')->groupBy('ejercicio_id')->get() as $g) {
    echo "ejercicio_id=" . $g->ejercicio_id . " -> " . $g->n . "\n";
}

echo "\n=== todos los riesgos con local_id R5 ===\n";
$r5 = DB::table('riesgos')->where('local_id', 'R5')->get();
foreach ($r5 as $r) echo "id=$r->id area=$r->area_id ej=$r->ejercicio_id local=$r->local_id upd=" . $r->updated_at . "\n";
if ($r5->isEmpty()) echo "(ninguno)\n";