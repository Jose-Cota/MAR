<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = DB::connection('poa_prod');

$proyecto = $conn->table('proyectos as py')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('urg.numero', '01')
    ->where('ro.numero', '02')
    ->where('pg.numero', '01')
    ->where('sp.numero', '01')
    ->where('py.numero', '01')
    ->select('py.proyecto_id')
    ->first();

if (!$proyecto) {
    echo "Proyecto no encontrado\n";
    exit;
}

echo "Proyecto ID: " . $proyecto->proyecto_id . "\n";

$alineaciones = $conn->table('pei_proyecto_alineaciones')->where('proyecto_id', $proyecto->proyecto_id)->get();
echo "Alineaciones en BD:\n";
print_r($alineaciones->toArray());
