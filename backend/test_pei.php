<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('py.numero', '04')
    ->where('sp.numero', '02')
    ->where('pg.numero', '01')
    ->where('ro.numero', '07')
    ->where('urg.numero', '03')
    ->select('py.proyecto_id', 'py.nombre', 'py.subprograma_id')
    ->get();

foreach($proyectos as $p) {
    echo "Proyecto ID: {$p->proyecto_id}, Nombre: {$p->nombre}, Subprograma ID: {$p->subprograma_id}\n";
    
    // Check PEI alineacion
    $alineacion = DB::table('pei_proyecto_alineaciones')->where('proyecto_id', $p->proyecto_id)->first();
    echo "Alineacion: " . json_encode($alineacion) . "\n";
    
    // Check subprograma alineacion
    $sp_alineacion = DB::table('subprograma_pei_alineaciones')->where('subprograma_id', $p->subprograma_id)->get();
    echo "Subprograma Alineacion: " . json_encode($sp_alineacion) . "\n";
}

