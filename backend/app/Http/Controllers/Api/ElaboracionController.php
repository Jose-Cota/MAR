<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
// We'll need an Export class later for Excel

class ElaboracionController extends Controller
{
    /**
     * Gráfica: Proyectos por Programa
     * Cuenta cuántos proyectos hay por cada programa en un ejercicio dado.
     */
    public function getGraficaProyectos(Request $request)
    {
        $ejercicioAnio = $request->input('ejercicio');

        if (!$ejercicioAnio) {
            return response()->json(['error' => 'El parámetro ejercicio es requerido'], 400);
        }
        
        $ejercicioRow = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $ejercicioAnio)->first();
        if (!$ejercicioRow) {
            return response()->json([]);
        }
        $ejercicio_id = $ejercicioRow->ejercicio_id;

        $datos = DB::connection('poa_prod')
            ->table('proyectos')
            ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
            ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
            ->select('programas.nombre as name', DB::raw('count(*) as value'))
            ->where('programas.ejercicio_id', $ejercicio_id)
            ->groupBy('programas.nombre')
            ->get();

        return response()->json($datos);
    }

    public function getMetasByProyecto(Request $request)
    {
        $proyecto_id = $request->input('proyecto_id');
        $tipo = $request->input('tipo');

        if (!$proyecto_id || !$tipo) {
            return response()->json(['error' => 'Faltan parámetros'], 400);
        }

        $metas = DB::connection('poa_prod')->table('metas')
            ->leftJoin('unidades_medidas', 'metas.unidad_medida_id', '=', 'unidades_medidas.unidad_medida_id')
            ->select('metas.meta_id', 'metas.numero', 'metas.meta', 'unidades_medidas.nombre as unidad_medida')
            ->where('metas.proyecto_id', $proyecto_id)
            ->where('metas.tipo', $tipo)
            ->get();

        return response()->json($metas);
    }

    public function getSubprogramasByPrograma(Request $request)
    {
        $programa_id = $request->input('programa_id');
        if (!$programa_id) {
            return response()->json(['error' => 'Falta programa_id'], 400);
        }

        $subprogramas = DB::connection('poa_prod')
            ->table('subprogramas')
            ->select('subprograma_id', 'numero', 'nombre')
            ->where('programa_id', $programa_id)
            ->get();

        return response()->json($subprogramas);
    }

    public function getProyectosBySubprograma(Request $request)
    {
        $subprograma_id = $request->input('subprograma_id');
        if (!$subprograma_id) {
            return response()->json(['error' => 'Falta subprograma_id'], 400);
        }

        $proyectos = DB::connection('poa_prod')
            ->table('proyectos')
            ->select('proyecto_id', 'numero', 'nombre')
            ->where('subprograma_id', $subprograma_id)
            ->get();

        return response()->json($proyectos);
    }

    /**
     * Tabla de Apertura Programática Mensual
     * Suma las metas agrupadas por mes.
     */
    public function getAperturaProgramatica(Request $request)
    {
        $ejercicioAnio = $request->input('ejercicio');
        $meta_id = $request->input('meta_id');

        if (!$ejercicioAnio) {
            return response()->json(['error' => 'El parámetro ejercicio es requerido'], 400);
        }
        
        $ejercicioRow = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $ejercicioAnio)->first();
        if (!$ejercicioRow) {
            return response()->json([
                'meses' => array_fill(1, 12, 0),
                'total' => 0
            ]);
        }
        $ejercicio_id = $ejercicioRow->ejercicio_id;

        $query = DB::connection('poa_prod')
            ->table('metas')
            ->join('meses_metas_programadas', 'metas.meta_id', '=', 'meses_metas_programadas.meta_id')
            ->join('proyectos', 'metas.proyecto_id', '=', 'proyectos.proyecto_id')
            ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
            ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
            ->where('programas.ejercicio_id', $ejercicio_id);

        if ($meta_id) {
            $query->where('metas.meta_id', $meta_id);
        }

        // Sumar programado por cada mes (1 = Enero, 12 = Diciembre)
        $meses = $query->select('meses_metas_programadas.mes_id', DB::raw('SUM(meses_metas_programadas.numero) as total_programado'))
            ->groupBy('meses_metas_programadas.mes_id')
            ->orderBy('meses_metas_programadas.mes_id')
            ->get();

        // Estructurar un array con los 12 meses por defecto
        $resultado = array_fill(1, 12, 0);
        foreach ($meses as $mes) {
            $resultado[$mes->mes_id] = (float) $mes->total_programado;
        }

        return response()->json([
            'meses' => $resultado,
            'total' => array_sum($resultado)
        ]);
    }

    public function exportarAperturaExcel(Request $request)
    {
        // En un caso real crearíamos una clase Maatwebsite\Excel\Concerns\FromCollection.
        // Por simplificación en este paso, retornamos el status y el archivo se construiría con Excel::download()
        // Requiere la clase Export específica, que armaremos después si el usuario lo necesita.
        return response()->json(['message' => 'Exportación Excel en construcción']);
    }
}
