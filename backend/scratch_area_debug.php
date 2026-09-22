<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "== riesgo 385 ==\n";
$r = DB::table('riesgos')->where('id', 385)->first();
foreach ((array)$r as $k=>$v) echo "  $k = $v\n";

echo "\n== riesgos con area_id NO numérica (todo ejercicio) ==\n";
$rows = DB::table('riesgos')->get()->filter(fn($x) => !ctype_digit((string)$x->area_id));
echo "  total=".$rows->count()."\n";
$byArea = $rows->groupBy('area_id');
foreach ($byArea as $area => $set) {
    $ej = $set->groupBy('ejercicio_id');
    $ejStr = collect($ej)->map(fn($v, $k) => "ej$k:".$v->count())->implode(' ');
    $pivoted = $set->filter(fn($x) => DB::table('actividad_riesgo')->where('riesgo_id', $x->id)->exists())->count();
    echo "  area=$area n=".$set->count()." [$ejStr] con_pivots=$pivoted\n";
}

echo "\n== riesgos area numérica 1..25 (2027, ej=19) con pivots ==\n";
$num = DB::table('riesgos')->where('ejercicio_id', 19)->get()->filter(fn($x) => ctype_digit((string)$x->area_id));
$pivoted = $num->filter(fn($x) => DB::table('actividad_riesgo')->where('riesgo_id', $x->id)->exists())->count();
echo "  total=".$num->count()." con_pivots=$pivoted\n";

echo "\n== local_ids duplicados entre sets (2027) ==\n";
$all = DB::table('riesgos')->where('ejercicio_id', 19)->get();
foreach (['R1','R2','R3','R4','R5','R6','R7','R8'] as $lid) {
    $withArea = $all->where('local_id', $lid);
    echo "  local_id=$lid n=".$withArea->count()." -> ".$withArea->map(fn($x) => "id={$x->id}(area={$x->area_id})")->implode(', ')."\n";
}