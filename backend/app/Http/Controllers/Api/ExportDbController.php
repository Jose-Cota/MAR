<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportDbController extends Controller
{
    public function export(Request $request)
    {
        try {
            $data = [
                'schemaVersion' => '1.0',
                'appName' => 'MAR_TECDMX',
                'backupDate' => now()->toIso8601String(),
                'ejercicios' => ['2026', '2027'],
                'datos' => []
            ];

            // Obtener ejercicios
            $ejerciciosDb = DB::table('ejercicios')->whereIn('ejercicio', ['2026', '2027'])->get();
            $ejercicioIds = $ejerciciosDb->pluck('ejercicio_id')->toArray();
            
            // Proyectos, Actividades, Riesgos, Factores, Controles, Indicadores
            foreach ($ejerciciosDb as $ej) {
                $ejercicioId = $ej->ejercicio_id;
                $year = $ej->ejercicio;
                
                $data['datos'][$year] = [
                    'riesgos' => [],
                    'actividades' => [],
                    'factores' => [],
                    'controles' => []
                ];
                // URG y RO
                try {
                    $urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $ejercicioId)->get();
                    $data['datos'][$year]['unidades_responsables_gastos'] = $urgs->toArray();
                } catch (\Exception $e) {
                    $data['datos'][$year]['unidades_responsables_gastos'] = [];
                    \Illuminate\Support\Facades\Log::warning("No se pudo conectar a poa_prod para exportar URGs: " . $e->getMessage());
                }
                
                $ros = DB::table('responsables_operativos')->get();
                $data['datos'][$year]['responsables_operativos'] = $ros->toArray();

                // Riesgos
                $riesgos = DB::table('riesgos')->where('ejercicio_id', $ejercicioId)->get();
                $data['datos'][$year]['riesgos'] = $riesgos->toArray();
                
                // Actividades Sustantivas (relacionadas a riesgos o proyectos)
                // Obtenemos actividades de proyectos de este ejercicio
                $proyectos = DB::table('proyectos')->where('ejercicio_id', $ejercicioId)->pluck('proyecto_id')->toArray();
                $actividades = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos)->get();
                $data['datos'][$year]['actividades'] = $actividades->toArray();
                
                // Controles de riesgo
                $riesgoIds = $riesgos->pluck('id')->toArray();
                if (!empty($riesgoIds)) {
                    $controles = DB::table('riesgo_controles')->whereIn('riesgo_id', $riesgoIds)->get();
                    $data['datos'][$year]['controles'] = $controles->toArray();
                }
                
                // Actividad_Riesgo (Pivot)
                $actividadIds = $actividades->pluck('id')->toArray(); // might be id or actividad_sustantiva_id
                $actividadIdsField = $actividades->first() ? (isset($actividades->first()->id) ? 'id' : 'actividad_sustantiva_id') : 'id';
                $aIds = $actividades->pluck($actividadIdsField)->toArray();
                
                if (!empty($aIds)) {
                    $actividadRiesgo = DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $aIds)->get();
                    $data['datos'][$year]['actividad_riesgo'] = $actividadRiesgo->toArray();
                }

                // Indicadores
                if (!empty($proyectos)) {
                    $indicadores = DB::table('indicadores')->whereIn('proyecto_id', $proyectos)->get();
                    $data['datos'][$year]['indicadores'] = $indicadores->toArray();
                }

                if (!empty($riesgoIds)) {
                    $riesgoIndicadores = DB::table('riesgo_indicadores')->whereIn('riesgo_id', $riesgoIds)->get();
                    $data['datos'][$year]['riesgo_indicadores'] = $riesgoIndicadores->toArray();
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error exportando BD: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno al exportar base de datos'], 500);
        }
    }
}
