<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Indicador;
use Illuminate\Http\Request;

class IndicadorController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'meta_id' => 'required|integer',
                'proyecto_id' => 'nullable|integer',
                'unidad_medida_id' => 'nullable|integer',
                'dimension_id' => 'nullable|integer',
                'frecuencia_id' => 'nullable|integer',
                'nombre' => 'required|string',
                'definicion' => 'nullable|string',
                'metodo_calculo' => 'nullable|string',
                'meta' => 'nullable|numeric',
                'id_metap' => 'nullable|integer',
                'id_metac' => 'nullable|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }

        $indicador = Indicador::create($validated);
        return response()->json($indicador, 201);
    }

    public function update(Request $request, $id)
    {
        $indicador = Indicador::findOrFail($id);

        try {
            $validated = $request->validate([
                'meta_id' => 'sometimes|integer',
                'proyecto_id' => 'nullable|integer',
                'unidad_medida_id' => 'nullable|integer',
                'dimension_id' => 'nullable|integer',
                'frecuencia_id' => 'nullable|integer',
                'nombre' => 'sometimes|string',
                'definicion' => 'nullable|string',
                'metodo_calculo' => 'nullable|string',
                'meta' => 'nullable|numeric',
                'id_metap' => 'nullable|integer',
                'id_metac' => 'nullable|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }

        $indicador->update($validated);
        return response()->json($indicador);
    }

    public function destroy($id)
    {
        $indicador = Indicador::findOrFail($id);
        $indicador->delete();
        return response()->json(null, 204);
    }
}
