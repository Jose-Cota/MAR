<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$a2026 = DB::table('actividades_sustantivas')->whereIn('proyecto_id', [1589, 1590, 1591, 1592, 1609, 1610])->count();
$o2026 = DB::table('acciones_sustantivas')->whereIn('proyecto_id', [1589, 1590, 1591, 1592, 1609, 1610])->count();
echo '2026 tiene ' . $a2026 . ' en actividades_sustantivas y ' . $o2026 . ' en acciones_sustantivas';
