<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')->where('ejercicio_id', 17)->pluck('proyecto_id');

$acts = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos)->count();
$accs = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectos)->count();

echo "For Ejercicio 17 (proyectos: " . count($proyectos) . "):\n";
echo "Count in actividades_sustantivas: $acts\n";
echo "Count in acciones_sustantivas: $accs\n";
