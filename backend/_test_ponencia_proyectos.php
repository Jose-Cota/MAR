<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos')
    ->where('ejercicio_id', 20) // 2027
    ->where('nombre', 'LIKE', '%Acciones que la Ponencia realiza%')
    ->get();

echo "Proyectos en 2027: " . count($proyectos) . "\n";
foreach ($proyectos as $p) {
    echo "- ID: " . $p->id . " Nombre: " . $p->nombre . " (RO ID: " . $p->responsable_operativo_id . ")\n";
}
