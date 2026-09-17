<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$yearsToFix = ['2026', '2027'];
$totalPeiCloned = 0;

foreach ($yearsToFix as $year) {
    $newProjects = DB::connection('poa_prod')
        ->table('proyectos as py')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->select('py.proyecto_id', 'py.nombre', 'ej.ejercicio_id')
        ->where('ej.ejercicio', $year)
        ->get();

    foreach ($newProjects as $newPy) {
        // Find 2025 equivalent
        $oldPy = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select('py.proyecto_id')
            ->where('ej.ejercicio', '2025')
            ->where('py.nombre', $newPy->nombre)
            ->first();

        if ($oldPy) {
            // Copy PEI alignment
            $oldPei = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $oldPy->proyecto_id)->first();
            if ($oldPei) {
                // Find matching PEI in new year
                // Program maps 1-1 by year
                $newPeiProg = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', $year)->first();
                if ($newPeiProg) {
                    // Match line by number
                    $oldLinea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_linea_estrategica_id', $oldPei->pei_linea_estrategica_id)->first();
                    if ($oldLinea) {
                        $newLinea = DB::connection('poa_prod')->table('pei_lineas_estrategicas')
                            ->where('pei_programa_id', $newPeiProg->pei_programa_id)
                            ->where('numero', $oldLinea->numero)->first();

                        if ($newLinea) {
                            $oldObj = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->where('pei_objetivo_estrategico_id', $oldPei->pei_objetivo_estrategico_id)->first();
                            if ($oldObj) {
                                $newObj = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')
                                    ->where('pei_linea_estrategica_id', $newLinea->pei_linea_estrategica_id)
                                    ->where('numero', $oldObj->numero)->first();
                                
                                if ($newObj) {
                                    DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->updateOrInsert(
                                        ['proyecto_id' => $newPy->proyecto_id],
                                        [
                                            'pei_programa_id' => $newPeiProg->pei_programa_id,
                                            'pei_linea_estrategica_id' => $newLinea->pei_linea_estrategica_id,
                                            'pei_objetivo_estrategico_id' => $newObj->pei_objetivo_estrategico_id,
                                        ]
                                    );
                                    $totalPeiCloned++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
echo "PEI Cloned: $totalPeiCloned\n";
