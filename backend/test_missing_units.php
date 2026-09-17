<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get all metas for 2027 projects
// (assuming we can identify 2027 projects by joining with proyectos -> urg -> ejercicios)
$missing_units = DB::connection('poa_prod')->select("
    SELECT DISTINCT m.unidad_medida_id, py.proyecto_id, py.numero as py_numero, urg.numero as urg_numero
    FROM metas m
    JOIN proyectos py ON m.proyecto_id = py.proyecto_id
    JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
    JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
    WHERE ej.ejercicio = 2027
    AND m.unidad_medida_id IS NOT NULL
    AND m.unidad_medida_id NOT IN (
        SELECT unidad_medida_id 
        FROM unidades_medidas 
        WHERE ejercicio_id = ej.ejercicio_id
    )
");

if (count($missing_units) > 0) {
    echo "Metas with missing unidades_medidas found:\n";
    print_r($missing_units);

    // Group by missing unidad_medida_id to show a concise summary
    $grouped = [];
    foreach ($missing_units as $row) {
        if (!isset($grouped[$row->unidad_medida_id])) {
            $grouped[$row->unidad_medida_id] = [];
        }
        $grouped[$row->unidad_medida_id][] = "Proyecto {$row->py_numero} (UR {$row->urg_numero})";
    }

    echo "\nSummary of missing unidades de medida:\n";
    foreach ($grouped as $um_id => $projects) {
        $projects_unique = array_unique($projects);
        echo "- Unidad de medida ID {$um_id} is missing. Used in: " . implode(', ', $projects_unique) . "\n";
    }
} else {
    echo "No other projects have missing unidades de medida for 2027.\n";
}
