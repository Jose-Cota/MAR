<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Query to get projects with their year (ejercicio)
$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id', 'py.numero', 'py.nombre', 'ej.ejercicio', 'urg.unidad_responsable_gasto_id')
    ->orderBy('urg.unidad_responsable_gasto_id')
    ->orderByRaw('CAST(py.numero AS INTEGER)')
    ->get();

$ejercicios = $proyectos->groupBy('ejercicio');

foreach ($ejercicios as $year => $projs) {
    echo "=== Ejercicio $year: " . count($projs) . " proyectos ===\n";
    // just print first 5 to see
    foreach ($projs->take(5) as $p) {
        echo "PY: {$p->numero} | ID: {$p->proyecto_id} | URG_ID: {$p->unidad_responsable_gasto_id} | {$p->nombre}\n";
    }
}

// Let's check how many projects have identical names in 2025 and 2026 to see if we can match them
$projs2025 = $ejercicios->get(2025) ?? collect();
$projs2026 = $ejercicios->get(2026) ?? collect();

echo "\nMatching by name and URG_ID...\n";
$mismatches = [];
$matches = 0;
foreach ($projs2026 as $p26) {
    $p25 = $projs2025->first(function($p25) use ($p26) {
        // Find matching project in 2025. It might have the same name, or we just compare by URG and some other logic
        // But URG IDs are different between years! URG for 2025 has one ID, URG for 2026 has another ID.
        return trim($p25->nombre) === trim($p26->nombre);
    });
    
    if ($p25) {
        if ((int)$p26->numero !== (int)$p25->numero) {
            $mismatches[] = "Mismatch: ID 2026 = {$p26->proyecto_id} | Name: {$p26->nombre} | 2026 num: {$p26->numero} | 2025 num: {$p25->numero}";
        } else {
            $matches++;
        }
    }
}
echo "Found $matches exact matches where numero is the same.\n";
echo "Found " . count($mismatches) . " mismatches where numero is different:\n";
foreach(array_slice($mismatches, 0, 10) as $m) echo $m."\n";
if (count($mismatches) > 10) echo "... and more.\n";

