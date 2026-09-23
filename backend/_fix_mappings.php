<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$fixes = [
    'POA 2026 – Dirección de Recursos Humanos' => 448,
    'POA 2026 – Dirección de Recursos Materiales y Servicios Generales' => 449,
    'POA 2026 – Dirección General Jurídica' => 453,
    'POA 2026 – Coordinación de Comunicación Social y Relaciones Públicas' => 468,
];

foreach ($fixes as $name => $roId) {
    DB::table('proyectos')
        ->where('ejercicio_id', 17)
        ->where('nombre', $name)
        ->update(['responsable_operativo_id' => $roId]);
    echo "Fixed $name to RO $roId\n";
}
