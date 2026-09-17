<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->select("
    SELECT py.proyecto_id, 
           urg.numero as urg_num, 
           p.numero as p_num, 
           sp.numero as sp_num,
           py.numero as py_num,
           py.nombre
    FROM proyectos py 
    JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id 
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id 
    JOIN ejercicios e ON urg.ejercicio_id = e.ejercicio_id 
    JOIN subprogramas sp ON py.subprograma_id = sp.subprograma_id 
    JOIN programas p ON sp.programa_id = p.programa_id 
    WHERE e.ejercicio = 2027 
    AND (p.numero = '02' OR p.numero = '2' OR sp.numero = '02' OR sp.numero = '2')
    AND urg.numero = '01' AND py.numero = '01'
");

foreach ($proyectos as $p) {
    echo "ID: {$p->proyecto_id} | U:{$p->urg_num} P:{$p->p_num} S:{$p->sp_num} Proy:{$p->py_num} | Nombre: {$p->nombre}\n";
}
