<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$n1 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 3)->value('nombre');
echo "ID 3: $n1\n";

$n2 = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 19)->where('unidad_responsable_gasto_id', '>=', 240)->where('nombre', $n1)->first();
if ($n2) echo "Found structural: " . $n2->unidad_responsable_gasto_id . " | " . $n2->nombre . "\n";
else echo "NOT FOUND structural\n";
