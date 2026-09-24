<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')
    ->where('ejercicio_id', 20)
    ->where('responsable_operativo_id', 969)
    ->get();

echo "Local DB Proyectos para RO 969 en 2027: " . count($proyectos) . "\n";
foreach ($proyectos as $p) {
    echo "- ID: " . $p->proyecto_id . " Nombre: " . $p->nombre . "\n";
}
