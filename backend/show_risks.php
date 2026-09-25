<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$riesgos = \Illuminate\Support\Facades\DB::select("SELECT id, local_id, riesgo, updated_at FROM riesgos WHERE status = 'Captura' AND area_id IN (SELECT unidad_responsable_gasto_id FROM unidades_responsables_gastos WHERE nombre LIKE '%Archivo%') ORDER BY updated_at DESC LIMIT 2");
print_r($riesgos);
