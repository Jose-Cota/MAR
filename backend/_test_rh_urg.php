<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('nombre', 'Dirección de Recursos Humanos')->where('unidad_responsable_gasto_id', '<', 240)->first();
echo "URG ID: " . $urg->unidad_responsable_gasto_id . "\n";

$proyectos = DB::connection('poa_prod')->table('proyectos')->where('urg_id', $urg->unidad_responsable_gasto_id)->where('ejercicio_id', 19)->get();
echo "Proyectos for URG " . $urg->unidad_responsable_gasto_id . ": " . count($proyectos) . "\n";
foreach ($proyectos as $p) {
    echo "- " . $p->nombre . "\n";
}
