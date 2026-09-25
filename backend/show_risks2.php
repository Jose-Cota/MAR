<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$riesgos = \Illuminate\Support\Facades\DB::select("SELECT id, local_id, riesgo, ejercicio_id, area_id FROM riesgos WHERE id IN (1271, 1273, 506, 507, 508, 509)");
print_r($riesgos);
