<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
foreach ([103, 152, 156, 177] as $id) {
    $n = DB::table('riesgos')->where('id', $id)->update(['ejercicio_id' => 17]);
    echo "id $id -> $n fila(s), ahora ejercicio_id=" . DB::table('riesgos')->where('id', $id)->value('ejercicio_id') . "\n";
}
echo "\nVerificación de que ya no quedan riesgos numéricos con año:\n";
$moved = DB::table('riesgos')->whereIn('ejercicio_id', [2026, 2027])->whereRaw('area_id REGEXP "^[0-9]+$"')->get();
echo $moved->isEmpty() ? "(ninguno)\n" : $moved->count() . " aún huérfanos\n";