<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $projectIds = [1421, 1464, 1423, 1425, 1466];
    $projectClaves = [
        1421 => '01.01.01.01.01',
        1464 => '01.01.01.02.01',
        1423 => '01.01.01.03.01',
        1425 => '01.01.01.04.01',
        1466 => '01.01.01.05.01'
    ];
    
    $actividadesPorProyecto = [];
    
    foreach ($projectIds as $pid) {
        $acts = DB::connection('poa_prod')->table('actividades_sustantivas')
            ->where('proyecto_id', $pid)
            ->orderBy('numero')
            ->pluck('descripcion')
            ->toArray();
            
        $actividadesPorProyecto[$pid] = $acts;
    }
    
    $baseActs = $actividadesPorProyecto[1421];
    $allSame = true;
    
    foreach ($projectIds as $pid) {
        if ($pid == 1421) continue;
        
        $diff1 = array_diff($baseActs, $actividadesPorProyecto[$pid]);
        $diff2 = array_diff($actividadesPorProyecto[$pid], $baseActs);
        
        if (!empty($diff1) || !empty($diff2)) {
            $allSame = false;
            echo "DIFERENCIA EN PROYECTO {$projectClaves[$pid]} (ID: $pid)\n";
            echo "Faltan o son diferentes comparado con 01.01.01.01.01:\n";
            print_r($diff1);
            echo "Tiene extra o diferente:\n";
            print_r($diff2);
            echo "-------------------------\n";
        }
    }
    
    if ($allSame) {
        echo "Sí, las 8 actividades sustantivas son EXACTAMENTE las mismas para los 5 proyectos.\n\n";
        echo "Actividades compartidas:\n";
        foreach ($baseActs as $idx => $act) {
            echo ($idx + 1) . ". " . trim($act) . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
