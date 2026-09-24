<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ejercicio_id = 2026;
$area_id = 1; // Presidencia

$query = App\Models\Riesgo::with(['controles', 'indicadores', 'seguimientos_mensuales', 'evaluaciones_trimestrales']);
$query->where('area_id', $area_id);

$val = $ejercicio_id;
$ej = Illuminate\Support\Facades\DB::table('ejercicios')->where('ejercicio', $val)->first();
if ($ej) {
    $query->where('ejercicio_id', $ej->ejercicio_id);
} else {
    $query->where('ejercicio_id', $val);
}

$riesgos = $query->get();
echo "Count for area $area_id: " . count($riesgos) . "\n";

$area_id = 17; // CCLA
$query = App\Models\Riesgo::with(['controles', 'indicadores', 'seguimientos_mensuales', 'evaluaciones_trimestrales']);
$query->where('area_id', $area_id);
$query->where('ejercicio_id', $ej->ejercicio_id);
$riesgos = $query->get();
echo "Count for area $area_id: " . count($riesgos) . "\n";
