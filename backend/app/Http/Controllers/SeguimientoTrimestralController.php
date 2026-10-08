<?php

namespace App\Http\Controllers;

use App\Models\SeguimientoTrimestral;
use Illuminate\Http\Request;

class SeguimientoTrimestralController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'riesgo_id' => 'required|exists:riesgos,id',
            'ejercicio_id' => 'required|integer',
            'trimestre' => 'required|integer|min:1|max:4',
            'm1_n' => 'nullable|numeric',
            'm1_d' => 'nullable|numeric',
            'm2_n' => 'nullable|numeric',
            'm2_d' => 'nullable|numeric',
            'm3_n' => 'nullable|numeric',
            'm3_d' => 'nullable|numeric',
            'notas' => 'nullable|string',
        ]);

        $seg = SeguimientoTrimestral::updateOrCreate(
            ['riesgo_id' => $data['riesgo_id'], 'ejercicio_id' => $data['ejercicio_id'], 'trimestre' => $data['trimestre']],
            $data
        );

        return response()->json($seg);
    }

    public function validar(Request $request, $id)
    {
        $seg = SeguimientoTrimestral::findOrFail($id);
        $seg->estatus = 'Validado';
        $seg->save();
        return response()->json($seg);
    }
}
