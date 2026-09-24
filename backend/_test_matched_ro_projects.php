<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')
    ->where('ejercicio_id', 20)
    ->whereIn('responsable_operativo_id', [411, 442, 969])
    ->get();

echo "Local DB Proyectos en 2027: " . count($proyectos) . "\n";
foreach ($proyectos as $p) {
    echo "- ID: " . $p->proyecto_id . " RO: " . $p->responsable_operativo_id . " Nombre: " . $p->nombre . "\n";
}
