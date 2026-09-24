<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ejercicio_db_id = 19;
$rgIds = [12, 40, 92, 118, 145, 169, 198, 231, 293, 324, 355, 386, 418, 449, 976];

$query = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', $ejercicio_db_id)
    ->whereIn('proyectos.responsable_operativo_id', $rgIds)
    ->select(
        'proyectos.*',
        'proyectos.proyecto_id as id',
        'responsables_operativos.unidad_responsable_gasto_id as urg_id',
        'responsables_operativos.nombre as ro_nombre'
    );

echo "Query: " . $query->toSql() . "\n";
print_r($query->getBindings());

$proyectos = $query->get();
echo "Proyectos found: " . count($proyectos) . "\n";
print_r($proyectos);
