<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiesgoInstitucional;
use Illuminate\Http\Request;

class RiesgoInstitucionalController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio_id = $request->query('ejercicio_id', 2026);
        
        $riesgos = RiesgoInstitucional::with('fuentes')
            ->where('ejercicio_id', $ejercicio_id)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $riesgos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ejercicio_id' => 'required|integer',
            'objetivo' => 'nullable|string',
            'riesgo' => 'required|string',
            'probabilidad' => 'required|integer|min:1|max:10',
            'impacto' => 'required|integer|min:1|max:10',
            'justificacion_valoracion' => 'nullable|string',
            'sourceRiskIds' => 'required|array',
            'sourceRiskIds.*' => 'integer|exists:riesgos,id'
        ]);

        // Determine next folio
        $ejercicio_id = $request->input('ejercicio_id');
        $count = RiesgoInstitucional::where('ejercicio_id', $ejercicio_id)->count() + 1;
        $folio = 'RI-' . $ejercicio_id . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);

        $riesgoInstitucional = RiesgoInstitucional::create([
            'folio' => $folio,
            'ejercicio_id' => $ejercicio_id,
            'objetivo' => $request->input('objetivo'),
            'riesgo' => $request->input('riesgo'),
            'factores' => $request->input('factores', ''),
            'probabilidad_sugerida' => $request->input('probabilidad_sugerida', null),
            'impacto_sugerido' => $request->input('impacto_sugerido', null),
            'probabilidad' => $request->input('probabilidad'),
            'impacto' => $request->input('impacto'),
            'justificacion_valoracion' => $request->input('justificacion_valoracion', ''),
            'estatus' => 'Proyecto'
        ]);

        $riesgoInstitucional->fuentes()->attach($request->input('sourceRiskIds'));

        $riesgoInstitucional->load('fuentes');

        return response()->json([
            'success' => true,
            'data' => $riesgoInstitucional
        ]);
    }

    public function show($id)
    {
        $riesgoInstitucional = RiesgoInstitucional::with(['fuentes' => function ($q) {
            $q->with(['controles', 'indicadores', 'proyectoBitacoras' => function ($q2) {
                $q2->join('poa_prod.proyectos', 'riesgo_proyecto_bitacora.proyecto_id', '=', 'poa_prod.proyectos.id_proyecto')
                   ->select('riesgo_proyecto_bitacora.*', 'poa_prod.proyectos.nombre_proyecto as proyecto_nombre');
            }]);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $riesgoInstitucional
        ]);
    }

    public function destroy($id)
    {
        $riesgoInstitucional = RiesgoInstitucional::findOrFail($id);
        $riesgoInstitucional->delete();
        
        return response()->json([
            'success' => true
        ]);
    }
}
