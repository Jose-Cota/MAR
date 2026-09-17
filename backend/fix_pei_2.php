<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$projects = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id', 'py.subprograma_id', 'ej.ejercicio', 'ej.ejercicio_id')
    ->whereIn('ej.ejercicio', ['2026', '2027'])
    ->get();

$count = 0;
foreach ($projects as $py) {
    // Check if it already has an alignment
    $existing = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $py->proyecto_id)->first();
    if ($existing) continue;

    // Look for alignment in subprograma_pei_alineaciones
    $align = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->where('subprograma_id', $py->subprograma_id)->first();
    if ($align) {
        // Need to map the PEI line/obj to the specific year's PEI programa
        $peiProg = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', $py->ejercicio)->first();
        if ($peiProg) {
            // we assume the IDs in subprograma_pei_alineaciones are generic or match 2026/2027 by name?
            // Wait, PeiMappingSeeder uses the CURRENT pei_lineas_estrategicas and pei_objetivos_estrategicos.
            // Which are unique per pei_programa! 
            // So subprograma_pei_alineaciones has IDs that belong to ONE specific pei_programa?
            $dbLinea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_linea_estrategica_id', $align->pei_linea_estrategica_id)->first();
            $dbObj = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->where('pei_objetivo_estrategico_id', $align->pei_objetivo_estrategico_id)->first();
            
            if ($dbLinea && $dbObj) {
                // Find matching by numero
                $newLinea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')
                    ->where('pei_programa_id', $peiProg->pei_programa_id)
                    ->where('numero', $dbLinea->numero)->first();
                    
                if ($newLinea) {
                    $newObj = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
                        ->where('pei_linea_estrategica_id', $newLinea->pei_linea_estrategica_id)
                        ->where('numero', $dbObj->numero)->first();
                        
                    if ($newObj) {
                        DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->insert([
                            'proyecto_id' => $py->proyecto_id,
                            'pei_programa_id' => $peiProg->pei_programa_id,
                            'pei_linea_estrategica_id' => $newLinea->pei_linea_estrategica_id,
                            'pei_objetivo_estrategico_id' => $newObj->pei_objetivo_estrategico_id
                        ]);
                        $count++;
                    }
                }
            }
        }
    }
}
echo "Aligned PEI for $count projects.\n";
