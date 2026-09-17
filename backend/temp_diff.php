<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->select('
    SELECT py.proyecto_id, py.numero as current_num, bak.numero as bak_num
    FROM proyectos py 
    JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id 
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id 
    JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id 
    JOIN proyectos_backup_21082026 bak ON py.proyecto_id = bak.proyecto_id
    WHERE ej.ejercicio = 2026
');

$diffs = [];
foreach ($proyectos as $p) {
    if ((int)$p->current_num !== (int)$p->bak_num) {
        $diffs[] = "ID: {$p->proyecto_id} | Current: {$p->current_num} | Backup: {$p->bak_num}";
    }
}

if (empty($diffs)) {
    echo "No differences found between current 2026 projects and the August 21 backup!\n";
} else {
    echo "Found " . count($diffs) . " differences:\n";
    foreach ($diffs as $diff) {
        echo $diff . "\n";
    }
}
