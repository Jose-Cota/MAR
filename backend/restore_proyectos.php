<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Query to get projects with their year (ejercicio) and urg_numero
$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id', 'py.numero', 'py.nombre', 'ej.ejercicio', 'urg.numero as urg_numero')
    ->orderBy('urg.unidad_responsable_gasto_id')
    ->orderByRaw('CAST(py.numero AS INTEGER)')
    ->get();

$ejercicios = $proyectos->groupBy('ejercicio');

$projs2025 = $ejercicios->get(2025) ?? collect();
$projs2026 = $ejercicios->get(2026) ?? collect();

echo "Matching by name and urg_numero...\n";
$mismatches = [];
$matches = 0;
$updates = [];

foreach ($projs2026 as $p26) {
    $matched2025 = $projs2025->filter(function($p25) use ($p26) {
        return trim($p25->nombre) === trim($p26->nombre) && $p25->urg_numero === $p26->urg_numero;
    });
    
    if ($matched2025->count() == 1) {
        $p25 = $matched2025->first();
        if ((int)$p26->numero !== (int)$p25->numero || strlen((string)$p26->numero) > 2) {
            $mismatches[] = "Mismatch: ID 2026 = {$p26->proyecto_id} | URG: {$p26->urg_numero} | Name: ".substr($p26->nombre, 0, 30)." | 2026 num: {$p26->numero} -> 2025 num: {$p25->numero}";
            $updates[] = [
                'proyecto_id' => $p26->proyecto_id,
                'nuevo_numero' => str_pad((int)$p25->numero, 2, '0', STR_PAD_LEFT)
            ];
        } else {
            $matches++;
        }
    } else if ($matched2025->count() > 1) {
        echo "Multiple matches for 2026 ID {$p26->proyecto_id} (URG {$p26->urg_numero})\n";
    } else {
        echo "No match found for 2026 ID {$p26->proyecto_id} (URG {$p26->urg_numero})\n";
    }
}
echo "Found $matches exact matches where numero is already correct.\n";
echo "Found " . count($mismatches) . " mismatches to update:\n";
foreach($mismatches as $m) echo $m."\n";

// Execute updates
echo "\nPerforming updates...\n";
$count = 0;
foreach ($updates as $upd) {
    DB::connection('poa_prod')->table('proyectos')
        ->where('proyecto_id', $upd['proyecto_id'])
        ->update(['numero' => $upd['nuevo_numero']]);
    $count++;
}
echo "Successfully restored $count projects to their 2025 numbering.\n";
