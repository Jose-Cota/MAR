<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$affected = \Illuminate\Support\Facades\DB::update(
    "UPDATE riesgos SET status = 'Captura' WHERE status = 'Borrador' AND area_id IN (SELECT unidad_responsable_gasto_id FROM unidades_responsables_gastos WHERE nombre LIKE '%Archivo%')"
);

echo "$affected rows updated.\n";
