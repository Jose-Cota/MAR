<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $ej2026 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2026)->first();
    $ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
    
    // Find project 01.01.01.01.01 in both exercises
    function getProjectByClave($ejId, $claveStr) {
        $parts = explode('.', $claveStr);
        if (count($parts) != 5) return null;
        return DB::connection('poa_prod')->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as p', 'sp.programa_id', '=', 'p.programa_id')
            ->where('urg.ejercicio_id', $ejId)
            ->where('p.numero', $parts[0])
            ->where('sp.numero', $parts[1])
            ->where('urg.numero', $parts[2])
            ->where('ro.numero', $parts[3])
            ->where('py.numero', $parts[4])
            ->select('py.*')
            ->first();
    }

    $p2026 = getProjectByClave($ej2026->ejercicio_id, '01.01.01.01.01');
    $p2027 = getProjectByClave($ej2027->ejercicio_id, '01.01.01.01.01');

    function checkDetails($p, $year) {
        if (!$p) {
            echo "No se encontró el proyecto en $year\n";
            return;
        }
        echo "=== PROYECTO $year (ID: {$p->proyecto_id}) ===\n";
        echo "Nombre: {$p->nombre}\n";
        
        $metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $p->proyecto_id)->get();
        echo "Metas: " . count($metas) . "\n";
        
        $inds = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $p->proyecto_id)->get();
        echo "Indicadores: " . count($inds) . "\n";
        
        $acts = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
        if(count($acts) == 0) {
            // Check acciones_sustantivas
            $acts = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            echo "Acciones Sustantivas: " . count($acts) . "\n";
        } else {
            echo "Actividades Sustantivas: " . count($acts) . "\n";
        }
    }

    checkDetails($p2026, 2026);
    echo "\n";
    checkDetails($p2027, 2027);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
