<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->select('
    SELECT py.proyecto_id, py.numero 
    FROM 0201sadpyrf_poa.proyectos py 
    JOIN 0201sadpyrf_poa.responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id 
    JOIN 0201sadpyrf_poa.unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id 
    JOIN 0201sadpyrf_poa.ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id 
    WHERE ej.ejercicio = 2026
');
print_r($proyectos);
