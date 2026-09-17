<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
$ejId = $ej2027->ejercicio_id;

$pys = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos as ro', 'proyectos.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('subprogramas as sp', 'proyectos.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as p', 'sp.programa_id', '=', 'p.programa_id')
    ->where('urg.ejercicio_id', $ejId)
    ->where('proyectos.numero', '01')
    ->select('proyectos.proyecto_id', 'proyectos.nombre', 'proyectos.numero as py_num', 'sp.numero as sp_num', 'p.numero as pg_num', 'urg.numero as urg_num', 'ro.numero as ro_num')
    ->get();

echo "=== FICHAS DESCRIPTIVAS CON PROYECTO 01 EN 2027 ===\n";
foreach($pys as $p) {
    $clave = $p->pg_num . '.' . $p->sp_num . '.' . $p->urg_num . '.' . $p->ro_num . '.' . $p->py_num;
    echo "- Clave: $clave | ID: {$p->proyecto_id} | {$p->nombre}\n";
}
