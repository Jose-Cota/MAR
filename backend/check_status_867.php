<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 867)->first();
echo "Proyecto 2026 (867):\n";
echo "Nombre: " . $py->nombre . "\n";
echo "Status: " . $py->status . "\n";
echo "Etapa: " . $py->etapa . "\n";

$py2027 = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1421)->first();
echo "Proyecto 2027 (1421):\n";
echo "Nombre: " . $py2027->nombre . "\n";
echo "Status: " . $py2027->status . "\n";
echo "Etapa: " . $py2027->etapa . "\n";
