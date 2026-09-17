<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$e2026 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2026)->first();

$urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $e2026->ejercicio_id)->first();
$ro = DB::connection('poa_prod')->table('responsables_operativos')->where('unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)->first();
$py = DB::connection('poa_prod')->table('proyectos')->where('responsable_operativo_id', $ro->responsable_operativo_id)->first();

$metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $py->proyecto_id)->get();
$inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $py->proyecto_id)->get();

echo "2026 Project ID: " . $py->proyecto_id . "\n";
echo "Metas count: " . count($metas) . "\n";
echo "Indicadores count: " . count($inds) . "\n";
