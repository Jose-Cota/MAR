<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$acts = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', 1767)->get();
echo "Presidencia (1767) count: " . count($acts) . "\n";
if(count($acts) > 0) echo "First desc: " . $acts[0]->descripcion . "\n";

$acts2 = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', 1772)->get();
echo "SA (1772) count: " . count($acts2) . "\n";
if(count($acts2) > 0) echo "First desc: " . $acts2[0]->descripcion . "\n";
