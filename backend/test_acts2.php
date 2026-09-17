<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find ANY actividades_sustantivas for a project named 'Control de Gestión Jurisdiccional'
$acts = DB::connection('poa_prod')->table('actividades_sustantivas')
    ->join('proyectos as py', 'actividades_sustantivas.proyecto_id', '=', 'py.proyecto_id')
    ->where('py.nombre', 'LIKE', '%Control de Gestión Jurisdiccional%')
    ->select('actividades_sustantivas.*', 'py.nombre as py_nombre')
    ->get();

echo "Actividades found by joining:\n" . json_encode($acts) . "\n";

$count = DB::table('actividades_sustantivas')->count();
echo "Total actividades in table: $count\n";

$countPei = DB::table('pei_proyecto_alineaciones')->count();
echo "Total PEI alineaciones in table: $countPei\n";

$tables = DB::connection('poa_prod')->getSchemaBuilder()->getAllTables();
foreach($tables as $t) {
    $name = array_values((array)$t)[0];
    if (strpos($name, 'actividad') !== false || strpos($name, 'sustantiva') !== false || strpos($name, 'pei') !== false) {
        echo "Table: $name\n";
    }
}
