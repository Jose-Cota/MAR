<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactorPeso;
use App\Models\Riesgo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactorPesoController extends Controller
{
    private function getInternalEjercicioId($year)
    {
        if ($year > 2000) {
            $ej = DB::table('ejercicios')->where('ejercicio', $year)->first();
            return $ej ? $ej->ejercicio_id : $year;
        }
        return $year;
    }

    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');

        $query = FactorPeso::query()->orderBy('ur_id', 'asc');

        if ($ejercicio) {
            $query->where('ejercicio', $ejercicio);
        }

        $factores = $query->get();

        return response()->json([
            'success' => true,
            'data' => $factores
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'factor_peso' => 'required|numeric|min:0',
        ]);

        $factor = FactorPeso::findOrFail($id);
        $factor->factor_peso = $request->input('factor_peso');
        $factor->save();

        // Recalcular puntaje para los riesgos asociados a esta UR y ejercicio
        $ejercicioId = $this->getInternalEjercicioId($factor->ejercicio);
        
        $riesgos = Riesgo::where('ejercicio_id', $ejercicioId)
            ->where('area_id', $factor->ur_id)
            ->get();

        foreach ($riesgos as $r) {
            $prob = $r->probabilidad ?? 0;
            $imp = $r->impacto ?? 0;
            $r->puntaje = round($prob * $imp * $factor->factor_peso, 2);
            $r->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Factor de peso actualizado y puntajes recalculados exitosamente',
            'data' => $factor
        ]);
    }

    public function recalcularPuntajes(Request $request)
    {
        $ejercicioInput = $request->input('ejercicio');
        $years = $ejercicioInput ? [$ejercicioInput] : [2026, 2027];

        $totalActualizados = 0;

        foreach ($years as $year) {
            $ejercicioId = $this->getInternalEjercicioId($year);
            $factores = FactorPeso::where('ejercicio', $year)->pluck('factor_peso', 'ur_id');

            $riesgos = Riesgo::where('ejercicio_id', $ejercicioId)->get();
            foreach ($riesgos as $r) {
                $peso = $factores[$r->area_id] ?? 1;
                $prob = $r->probabilidad ?? 0;
                $imp = $r->impacto ?? 0;
                $r->puntaje = round($prob * $imp * $peso, 2);
                $r->save();
                $totalActualizados++;
            }

            // Recalcular ponderaciones por área
            $areas = $riesgos->pluck('area_id')->unique();
            foreach ($areas as $areaId) {
                Riesgo::recalcularPonderacionesUR($areaId, $ejercicioId);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se recalcularon puntajes y ponderaciones para {$totalActualizados} riesgos.",
            'total' => $totalActualizados
        ]);
    }
}
