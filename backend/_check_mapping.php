<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos2026 = DB::table('proyectos')->where('ejercicio_id', 17)->get();
$proyectos2027 = DB::table('proyectos')->where('ejercicio_id', 19)->get();

echo "2026 Projects: " . count($proyectos2026) . "\n";
echo "2027 Projects: " . count($proyectos2027) . "\n";

foreach ($proyectos2027 as $p) {
    echo "2027 Project: {$p->nombre} (RO: {$p->responsable_operativo_id})\n";
}
