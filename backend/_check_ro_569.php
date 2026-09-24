<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$ros = DB::table('responsables_operativos')
    ->where('ejercicio_id', 19)
    ->where('unidad_responsable_gasto_id', 569)
    ->get();
echo "Found " . count($ros) . " ROs for URG 569\n";
