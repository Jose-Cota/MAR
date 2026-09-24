<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$anomalies = DB::connection('poa_prod')->select("
    SELECT ar.actividad_sustantiva_id, r.ejercicio_id
    FROM actividad_riesgo ar
    JOIN riesgos r ON ar.riesgo_id = r.id
    WHERE r.ejercicio_id >= 19 AND ar.actividad_sustantiva_id NOT IN (SELECT id FROM actividades_sustantivas)
");

$anomalies2 = DB::connection('poa_prod')->select("
    SELECT ar.actividad_sustantiva_id, r.ejercicio_id
    FROM actividad_riesgo ar
    JOIN riesgos r ON ar.riesgo_id = r.id
    WHERE r.ejercicio_id < 19 AND ar.actividad_sustantiva_id NOT IN (SELECT accion_sustantiva_id FROM acciones_sustantivas)
");

echo "Anomalies (2027+ risk pointing to non-existent actividad): " . count($anomalies) . "\n";
echo "Anomalies (Pre-2027 risk pointing to non-existent accion): " . count($anomalies2) . "\n";
