<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FichaPoaController extends Controller
{
    /**
     * Devuelve el listado de proyectos con sus claves (UR, RO, PG, SP, PY)
     * para la tabla de la pestaña Fichas POA.
     */
    public function getListaFichas(Request $request)
    {
        $ejercicioAnio = $request->input('ejercicio');
        $ejercicio_id = null;
        if ($ejercicioAnio) {
            $ejercicioRow = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $ejercicioAnio)->first();
            if ($ejercicioRow) {
                $ejercicio_id = $ejercicioRow->ejercicio_id;
            } else {
                return response()->json([]);
            }
        }

        $query = DB::connection('poa_prod')
            ->table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
            ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
            ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
            ->select(
                'proyectos.proyecto_id as id',
                'unidades_responsables_gastos.numero as urg',
                'responsables_operativos.numero as ro',
                'programas.numero as pg',
                'subprogramas.numero as sp',
                'proyectos.numero as py',
                'proyectos.nombre as denominacion'
            );

        if ($ejercicio_id) {
            $query->where('programas.ejercicio_id', $ejercicio_id);
        }

        return response()->json($query->get());
    }

    /**
     * Genera el PDF de Fichas POA para los proyectos seleccionados.
     */
    public function generarFichasPdf(Request $request)
    {
        $ids = $request->input('proyectos', []); // array of IDs
        
        if (empty($ids)) {
            return response()->json(['error' => 'No se seleccionaron proyectos'], 400);
        }

        // Obtener la información de los proyectos seleccionados
        $proyectos = DB::connection('poa_prod')
            ->table('proyectos')
            ->whereIn('proyecto_id', $ids)
            ->get();
            
        // Por simplificación en este paso (como indica el plan de implementación)
        // Solo retornamos que se recibió correctamente. 
        // En una implementación final generaríamos el HTML y usaríamos Pdf::loadHTML()
        // o Pdf::loadView() y luego ->download()
        
        // Simular generación de PDF y devolver un archivo de prueba
        $pdf = Pdf::loadHTML('<h1>Fichas POA Generadas</h1><p>Generando ' . count($ids) . ' fichas...</p>');
        return $pdf->download('fichas_poa.pdf');
    }
}
