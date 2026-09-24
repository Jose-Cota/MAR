<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$acts = DB::table('acciones_sustantivas')->where('proyecto_id', 1596)->get();
echo "DGJ 2026 tiene " . count($acts) . " actividades en acciones_sustantivas\n";

$acts2 = DB::table('actividades_sustantivas')->where('proyecto_id', 1596)->get();
echo "DGJ 2026 tiene " . count($acts2) . " actividades en actividades_sustantivas\n";
