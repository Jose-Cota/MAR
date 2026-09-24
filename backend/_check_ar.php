<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ar = DB::connection('poa_prod')->select("SELECT actividad_sustantiva_id FROM actividad_riesgo");
$ar_ids = array_column($ar, 'actividad_sustantiva_id');

$in_act = DB::connection('poa_prod')->table('actividades_sustantivas')->whereIn('id', $ar_ids)->count();
$in_acc = DB::connection('poa_prod')->table('acciones_sustantivas')->whereIn('accion_sustantiva_id', $ar_ids)->count();
$in_both = DB::connection('poa_prod')->select("
    SELECT ar.actividad_sustantiva_id 
    FROM actividad_riesgo ar
    JOIN actividades_sustantivas act ON ar.actividad_sustantiva_id = act.id
    JOIN acciones_sustantivas acc ON ar.actividad_sustantiva_id = acc.accion_sustantiva_id
");

echo "Total in actividad_riesgo: " . count($ar_ids) . "\n";
echo "Found in actividades_sustantivas: $in_act\n";
echo "Found in acciones_sustantivas: $in_acc\n";
echo "Found in BOTH (collision in actividad_riesgo!): " . count($in_both) . "\n";
