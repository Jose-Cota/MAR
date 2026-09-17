<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = DB::connection('poa_prod');

$proyectos = $conn->table('proyectos as py')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
    ->select('py.proyecto_id', 'py.subprograma_id', 'ej.ejercicio')
    ->whereIn('ej.ejercicio', ['2026', '2027'])
    ->get();

$synced = 0;
foreach ($proyectos as $py) {
    // Obtener las alineaciones correspondientes al subprograma del proyecto
    $sub_aligns = $conn->table('subprograma_pei_alineaciones')
        ->where('subprograma_id', $py->subprograma_id)
        ->get();
        
    if ($sub_aligns->isNotEmpty()) {
        // Borrar las antiguas para sincronizar las nuevas múltiples
        $conn->table('pei_proyecto_alineaciones')->where('proyecto_id', $py->proyecto_id)->delete();
        
        $pei_prog = $conn->table('pei_programas')->where('ejercicio_anio', $py->ejercicio)->first();
        if ($pei_prog) {
            foreach ($sub_aligns as $sa) {
                // Como subprograma_pei_alineaciones puede estar mapeado a un PEI específico,
                // empatamos por "número" para que funcione para 2026 y 2027
                $orig_linea = $conn->table('pei_lineas_estrategicas')->where('pei_linea_estrategica_id', $sa->pei_linea_estrategica_id)->first();
                $orig_obj = $conn->table('pei_objetivos_estrategicos')->where('pei_objetivo_estrategico_id', $sa->pei_objetivo_estrategico_id)->first();
                
                if ($orig_linea && $orig_obj) {
                    $new_linea = $conn->table('pei_lineas_estrategicas')
                        ->where('pei_programa_id', $pei_prog->pei_programa_id)
                        ->where('numero', $orig_linea->numero)->first();
                        
                    if ($new_linea) {
                        $new_obj = $conn->table('pei_objetivos_estrategicos')
                            ->where('pei_linea_estrategica_id', $new_linea->pei_linea_estrategica_id)
                            ->where('numero', $orig_obj->numero)->first();
                            
                        if ($new_obj) {
                            $conn->table('pei_proyecto_alineaciones')->insert([
                                'proyecto_id' => $py->proyecto_id,
                                'pei_programa_id' => $pei_prog->pei_programa_id,
                                'pei_linea_estrategica_id' => $new_linea->pei_linea_estrategica_id,
                                'pei_objetivo_estrategico_id' => $new_obj->pei_objetivo_estrategico_id,
                            ]);
                            $synced++;
                        }
                    }
                }
            }
        }
    }
}
echo "Se han sincronizado correctamente $synced alineaciones de los subprogramas hacia los proyectos.\n";
