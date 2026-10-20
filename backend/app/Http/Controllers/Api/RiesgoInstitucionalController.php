<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiesgoInstitucional;
use Illuminate\Http\Request;

class RiesgoInstitucionalController extends Controller
{
    private function getInternalEjercicioId($val)
    {
        if ($val > 2000) {
            $ej = \Illuminate\Support\Facades\DB::table('ejercicios')->where('ejercicio', $val)->first();
            return $ej ? $ej->ejercicio_id : $val;
        }
        return $val;
    }

    public function index(Request $request)
    {
        $val = $request->query('ejercicio_id', 2026);
        $ejercicio_id = $this->getInternalEjercicioId($val);
        
        $riesgos = RiesgoInstitucional::with('fuentes')
            ->where('ejercicio_id', $ejercicio_id)
            ->get();

        foreach ($riesgos as $ri) {
            if ($ri->fuentes->count() > 0 && ($ri->probabilidad === null || $ri->impacto === null || $ri->probabilidad == 0 || $ri->impacto == 0)) {
                $avgP = (int) round($ri->fuentes->avg('probabilidad'));
                $avgI = (int) round($ri->fuentes->avg('impacto'));
                $ri->update([
                    'probabilidad_sugerida' => $avgP,
                    'impacto_sugerido' => $avgI,
                    'probabilidad' => $avgP,
                    'impacto' => $avgI
                ]);
                $ri->probabilidad = $avgP;
                $ri->impacto = $avgI;
                $ri->probabilidad_sugerida = $avgP;
                $ri->impacto_sugerido = $avgI;
            }
        }
            
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
            'probabilidad' => 'nullable|integer|min:0|max:10',
            'impacto' => 'nullable|integer|min:0|max:10',
            'justificacion_valoracion' => 'nullable|string',
            'sourceRiskIds' => 'nullable|array',
            'sourceRiskIds.*' => 'integer|exists:riesgos,id'
        ]);

        // Determine next folio
        $input_ejercicio = $request->input('ejercicio_id');
        $ejercicio_id = $this->getInternalEjercicioId($input_ejercicio);
        
        $yearPrefix = $input_ejercicio > 2000 ? $input_ejercicio : 2026;
        $count = RiesgoInstitucional::where('ejercicio_id', $ejercicio_id)->count() + 1;
        $folio = 'RI-' . $yearPrefix . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        while (RiesgoInstitucional::where('folio', $folio)->exists()) {
            $count++;
            $folio = 'RI-' . $yearPrefix . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        }

        $riesgoInstitucional = RiesgoInstitucional::create([
            'folio' => $folio,
            'ejercicio_id' => $ejercicio_id,
            'objetivo' => $request->input('objetivo'),
            'riesgo' => $request->input('riesgo'),
            'factores' => $request->input('factores', ''),
            'control' => $request->input('control', ''),
            'indicador' => $request->input('indicador', ''),
            'probabilidad_sugerida' => $request->input('probabilidad_sugerida', null),
            'impacto_sugerido' => $request->input('impacto_sugerido', null),
            'probabilidad' => $request->input('probabilidad', null),
            'impacto' => $request->input('impacto', null),
            'justificacion_valoracion' => $request->input('justificacion_valoracion', ''),
            'estatus' => 'Proyecto'
        ]);

        if ($request->has('sourceRiskIds') && is_array($request->input('sourceRiskIds')) && count($request->input('sourceRiskIds')) > 0) {
            $riesgoInstitucional->fuentes()->attach($request->input('sourceRiskIds'));
            $this->recalcularPromedios($riesgoInstitucional->id);
        }

        $riesgoInstitucional->load('fuentes');

        return response()->json([
            'success' => true,
            'data' => $riesgoInstitucional
        ]);
    }

    public function update(Request $request, $id)
    {
        $riesgoInstitucional = RiesgoInstitucional::findOrFail($id);

        $request->validate([
            'objetivo' => 'nullable|string',
            'riesgo' => 'required|string',
            'factores' => 'nullable|string',
            'control' => 'nullable|string',
            'indicador' => 'nullable|string',
            'probabilidad' => 'nullable|integer|min:0|max:10',
            'impacto' => 'nullable|integer|min:0|max:10',
            'justificacion_valoracion' => 'nullable|string',
            'sourceRiskIds' => 'nullable|array',
            'sourceRiskIds.*' => 'integer|exists:riesgos,id'
        ]);

        $riesgoInstitucional->update([
            'objetivo' => $request->has('objetivo') ? $request->input('objetivo') : $riesgoInstitucional->objetivo,
            'riesgo' => $request->has('riesgo') ? $request->input('riesgo') : $riesgoInstitucional->riesgo,
            'factores' => $request->has('factores') ? $request->input('factores') : $riesgoInstitucional->factores,
            'control' => $request->has('control') ? $request->input('control') : $riesgoInstitucional->control,
            'indicador' => $request->has('indicador') ? $request->input('indicador') : $riesgoInstitucional->indicador,
            'probabilidad_sugerida' => $request->has('probabilidad_sugerida') ? $request->input('probabilidad_sugerida') : $riesgoInstitucional->probabilidad_sugerida,
            'impacto_sugerido' => $request->has('impacto_sugerido') ? $request->input('impacto_sugerido') : $riesgoInstitucional->impacto_sugerido,
            'probabilidad' => $request->has('probabilidad') ? $request->input('probabilidad') : $riesgoInstitucional->probabilidad,
            'impacto' => $request->has('impacto') ? $request->input('impacto') : $riesgoInstitucional->impacto,
            'ponderacion' => $request->has('ponderacion') ? $request->input('ponderacion') : $riesgoInstitucional->ponderacion,
            'ponderacion_probabilidad' => $request->has('ponderacion_probabilidad') ? $request->input('ponderacion_probabilidad') : $riesgoInstitucional->ponderacion_probabilidad,
            'ponderacion_impacto' => $request->has('ponderacion_impacto') ? $request->input('ponderacion_impacto') : $riesgoInstitucional->ponderacion_impacto,
            'justificacion_valoracion' => $request->has('justificacion_valoracion') ? $request->input('justificacion_valoracion') : $riesgoInstitucional->justificacion_valoracion,
        ]);

        if ($request->has('sourceRiskIds')) {
            $riesgoInstitucional->fuentes()->sync($request->input('sourceRiskIds'));
            $this->recalcularPromedios($riesgoInstitucional->id);
        }

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

    public function assignFuente(Request $request)
    {
        $request->validate([
            'riesgo_id' => 'required|integer|exists:riesgos,id',
            'riesgo_institucional_id' => 'nullable'
        ]);

        $riesgoId = $request->input('riesgo_id');
        $riId = $request->input('riesgo_institucional_id');

        $prevRiId = \Illuminate\Support\Facades\DB::table('riesgo_institucional_fuente')
            ->where('riesgo_id', $riesgoId)
            ->value('riesgo_institucional_id');

        \Illuminate\Support\Facades\DB::table('riesgo_institucional_fuente')
            ->where('riesgo_id', $riesgoId)
            ->delete();

        if ($riId) {
            \Illuminate\Support\Facades\DB::table('riesgo_institucional_fuente')->insert([
                'riesgo_institucional_id' => $riId,
                'riesgo_id' => $riesgoId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->recalcularPromedios($riId);
        }

        if ($prevRiId && $prevRiId != $riId) {
            $this->recalcularPromedios($prevRiId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vinculación de riesgo institucional actualizada'
        ]);
    }

    public function recalcularPromedios($riId)
    {
        $ri = RiesgoInstitucional::with('fuentes')->find($riId);
        if (!$ri) return null;

        if ($ri->fuentes->count() > 0) {
            $avgP = (int) round($ri->fuentes->avg('probabilidad'));
            $avgI = (int) round($ri->fuentes->avg('impacto'));
            $ri->update([
                'probabilidad_sugerida' => $avgP,
                'impacto_sugerido' => $avgI,
                'probabilidad' => $avgP,
                'impacto' => $avgI
            ]);
        } else {
            $ri->update([
                'probabilidad_sugerida' => null,
                'impacto_sugerido' => null,
                'probabilidad' => null,
                'impacto' => null
            ]);
        }
        RiesgoInstitucional::recalcularPonderaciones($riId);
        return $ri->fresh();
    }

    public function calcularPonderaciones(Request $request)
    {
        $val = $request->input('ejercicio_id', $request->query('ejercicio_id', 2026));
        $ejercicio_id = $this->getInternalEjercicioId($val);
        $year = ($ejercicio_id == 19 || $ejercicio_id == 2027) ? 2027 : 2026;

        RiesgoInstitucional::recalcularPonderaciones();

        return response()->json([
            'success' => true,
            'message' => 'Ponderaciones institucionales actualizadas exitosamente.',
            'data' => $this->generarMemoriaCalculo($ejercicio_id, $year)
        ]);
    }

    public function memoriaCalculo(Request $request)
    {
        $val = $request->query('ejercicio_id', 2026);
        $ejercicio_id = $this->getInternalEjercicioId($val);
        $year = ($ejercicio_id == 19 || $ejercicio_id == 2027) ? 2027 : 2026;

        return response()->json([
            'success' => true,
            'data' => $this->generarMemoriaCalculo($ejercicio_id, $year)
        ]);
    }

    private function generarMemoriaCalculo($ejercicio_id, $year)
    {
        $factores = \Illuminate\Support\Facades\DB::table('factor_peso')
            ->where('ejercicio', $year)
            ->pluck('factor_peso', 'ur_id')
            ->toArray();

        $areas = \App\Models\Area::pluck('nombre', 'area_id')->toArray();

        $ris = RiesgoInstitucional::with(['fuentes' => function ($q) {
            $q->orderBy('area_id')->orderBy('local_id');
        }])
        ->where('ejercicio_id', $ejercicio_id)
        ->orderBy('folio')
        ->get();

        $memoria = [];

        foreach ($ris as $ri) {
            $sumatoria = (float) $ri->fuentes->sum('puntaje');
            $fuentesDetalle = [];

            $totProb = 0;
            $totImp = 0;

            foreach ($ri->fuentes as $f) {
                $peso = (int) ($factores[$f->area_id] ?? 1);
                $puntaje = (float) $f->puntaje;
                $proporcion = $sumatoria > 0 ? round($puntaje / $sumatoria, 4) : 0;
                $aporteProb = $sumatoria > 0 ? round(($puntaje / $sumatoria) * (float)$f->probabilidad, 4) : 0;
                $aporteImp = $sumatoria > 0 ? round(($puntaje / $sumatoria) * (float)$f->impacto, 4) : 0;

                $totProb += $aporteProb;
                $totImp += $aporteImp;

                $fuentesDetalle[] = [
                    'rur_id' => $f->id,
                    'local_id' => $f->local_id,
                    'area_id' => $f->area_id,
                    'area_nombre' => $areas[$f->area_id] ?? ("Área " . $f->area_id),
                    'probabilidad' => (int) $f->probabilidad,
                    'impacto' => (int) $f->impacto,
                    'peso_ur' => $peso,
                    'puntaje' => $puntaje,
                    'proporcion' => $proporcion,
                    'aporte_probabilidad' => $aporteProb,
                    'aporte_impacto' => $aporteImp,
                ];
            }

            $memoria[] = [
                'ri_id' => $ri->id,
                'folio' => $ri->folio,
                'riesgo' => $ri->riesgo,
                'objetivo' => $ri->objetivo,
                'sumatoria_pra' => round($sumatoria, 2),
                'ponderacion_probabilidad' => (float) $ri->ponderacion_probabilidad,
                'ponderacion_impacto' => (float) $ri->ponderacion_impacto,
                'total_fuentes' => count($fuentesDetalle),
                'fuentes' => $fuentesDetalle,
            ];
        }

        return $memoria;
    }
}
