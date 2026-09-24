<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$acts = DB::table('acciones_sustantivas')->whereIn('proyecto_id', [1611, 1612])->get();
echo "PJHR 2026 tiene " . count($acts) . " actividades en acciones_sustantivas\n";
