<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $conn = DB::connection()->getPdo();
    echo "CONNECTED to: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit;
}

$count = DB::table('riesgos')->count();
echo "riesgos: " . $count . "\n";
echo "riesgo_controles: " . DB::table('riesgo_controles')->count() . "\n";
echo "riesgo_indicadores: " . DB::table('riesgo_indicadores')->count() . "\n";

$cols = DB::select('SHOW COLUMNS FROM riesgo_indicadores');
echo "\nriesgo_indicadores columns:\n";
foreach ($cols as $c) echo '  ' . $c->Field . ' (' . $c->Type . ')' . "\n";

$cols2 = DB::select('SHOW COLUMNS FROM riesgo_controles');
echo "\nriesgo_controles columns:\n";
foreach ($cols2 as $c) echo '  ' . $c->Field . ' (' . $c->Type . ')' . "\n";

echo "\nSample riesgos (local_id, area_id, ejercicio_id):\n";
$rows = DB::table('riesgos')->select('id','local_id','area_id','ejercicio_id')->limit(6)->get();
foreach ($rows as $r) echo "  {$r->id} {$r->local_id} area={$r->area_id} ej={$r->ejercicio_id}\n";

echo "\nSample riesgo_indicadores:\n";
$rows = DB::table('riesgo_indicadores')
    ->join('riesgos','riesgos.id','=','riesgo_indicadores.riesgo_id')
    ->select('riesgo_indicadores.*','riesgos.local_id')
    ->limit(4)->get();
foreach ($rows as $r) echo "  {$r->local_id} | nombre={$r->nombre} | formula={$r->formula} | num={$r->numerador} | den={$r->denominador} | per={$r->periodicidad}\n";

echo "\nSample riesgo_controles:\n";
$rows = DB::table('riesgo_controles')
    ->join('riesgos','riesgos.id','=','riesgo_controles.riesgo_id')
    ->select('riesgo_controles.*','riesgos.local_id')
    ->limit(4)->get();
foreach ($rows as $r) echo "  {$r->local_id} | texto=" . mb_substr($r->texto,0,60) . "\n";