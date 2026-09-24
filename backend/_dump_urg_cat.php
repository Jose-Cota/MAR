<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$u = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', '<', 15)->get();
foreach($u as $x) echo $x->unidad_responsable_gasto_id . ' - ' . $x->nombre . "\n";
