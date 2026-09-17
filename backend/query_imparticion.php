<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = DB::connection('poa_prod');

$programa = $conn->table('programas')->where('nombre', 'like', '%Impartici%n de Justicia%')->first();
if (!$programa) {
    echo "Programa no encontrado\n";
    exit;
}

echo "Programa: " . $programa->numero . " - " . $programa->nombre . "\n";

$subprogramas = $conn->table('subprogramas')->where('programa_id', $programa->programa_id)->get();

foreach ($subprogramas as $sp) {
    echo "\nSubprograma: " . $sp->numero . " - " . $sp->nombre . "\n";
    
    // Fallback in case subprograma_pei_alineaciones is not in poa_prod but default
    $alineaciones = DB::table('subprograma_pei_alineaciones as spa')
        ->join('pei_lineas_estrategicas as l', 'spa.pei_linea_estrategica_id', '=', 'l.pei_linea_estrategica_id')
        ->join('pei_objetivos_estrategicos as o', 'spa.pei_objetivo_estrategico_id', '=', 'o.pei_objetivo_estrategico_id')
        ->where('spa.subprograma_id', $sp->subprograma_id)
        ->select('l.numero as linea_num', 'l.nombre as linea_nom', 'o.numero as obj_num', 'o.nombre as obj_nom')
        ->get();
        
    if ($alineaciones->isEmpty()) {
        echo "  (Sin alineaciones configuradas en subprograma_pei_alineaciones)\n";
    } else {
        foreach ($alineaciones as $a) {
            echo "  - Línea " . $a->linea_num . " (" . $a->linea_nom . ") -> Objetivo " . $a->obj_num . " (" . $a->obj_nom . ")\n";
        }
    }
}
