<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos2026 = [1745, 1746]; // PAAH 2026
$acts = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectos2026)->get();
echo "PAAH 2026 tiene " . count($acts) . " actividades:\n";
foreach($acts as $a) {
    echo "- " . $a->descripcion . "\n";
}
