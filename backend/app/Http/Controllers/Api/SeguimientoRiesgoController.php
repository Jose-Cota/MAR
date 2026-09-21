<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiesgoSeguimientoMensual;
use App\Models\RiesgoEvaluacionTrimestral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeguimientoRiesgoController extends Controller
{
    public function seguimientoMensual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'riesgo_id' => 'required|exists:riesgos,id',
            'ejercicio_id' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
            'numerador' => 'nullable|numeric',
            'denominador' => 'nullable|numeric',
            'valor' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $seguimiento = RiesgoSeguimientoMensual::updateOrCreate(
            [
                'riesgo_id' => $request->riesgo_id,
                'mes' => $request->mes,
            ],
            [
                'ejercicio_id' => $request->ejercicio_id,
                'numerador' => $request->numerador,
                'denominador' => $request->denominador,
                'valor' => $request->valor,
            ]
        );

        return response()->json($seguimiento);
    }

    public function evaluacionTrimestral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'riesgo_id' => 'required|exists:riesgos,id',
            'ejercicio_id' => 'required|integer',
            'trimestre' => 'required|integer|min:1|max:4',
            'probabilidad' => 'required|integer|min:0|max:10',
            'impacto' => 'required|integer|min:0|max:10',
            'etiqueta' => 'nullable|string',
            'evidencia_control' => 'nullable|string',
            'incidencia' => 'nullable|string',
            'accion_mitigacion' => 'nullable|string',
            'estatus' => 'nullable|string',
            'responsable' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $evaluacion = RiesgoEvaluacionTrimestral::updateOrCreate(
            [
                'riesgo_id' => $request->riesgo_id,
                'trimestre' => $request->trimestre,
            ],
            [
                'ejercicio_id' => $request->ejercicio_id,
                'probabilidad' => $request->probabilidad,
                'impacto' => $request->impacto,
                'etiqueta' => $request->etiqueta,
                'evidencia_control' => $request->evidencia_control,
                'incidencia' => $request->incidencia,
                'accion_mitigacion' => $request->accion_mitigacion,
                'estatus' => $request->estatus,
                'responsable' => $request->responsable,
            ]
        );

        return response()->json($evaluacion);
    }
}
