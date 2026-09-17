<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->select('
    SELECT py.proyecto_id, py.numero 
    FROM proyectos_backup_21082026 py 
    JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id 
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id 
    JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id 
    WHERE ej.ejercicio = 2026
    ORDER BY CAST(py.numero AS INTEGER) ASC
');
foreach ($proyectos as $p) {
    echo "ID: {$p->proyecto_id} | Num: {$p->numero}\n";
}
