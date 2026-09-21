<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Riesgo;
use App\Models\ActividadSustantiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiesgoController extends Controller
{
    public function index(Request $request)
    {
        $query = Riesgo::with(['controles', 'indicadores', 'actividades', 'seguimientos_mensuales', 'evaluaciones_trimestrales']);

        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->has('ejercicio_id')) {
            // Check if the passed value is a year (e.g. 2027) instead of an ID
            $val = $request->ejercicio_id;
            if ($val > 2000) {
                $ej = DB::table('ejercicios')->where('ejercicio', $val)->first();
                if ($ej) {
                    $query->where('ejercicio_id', $ej->ejercicio_id);
                } else {
                    $query->where('ejercicio_id', $val);
                }
            } else {
                $query->where('ejercicio_id', $val);
            }
        }

        $riesgos = $query->get();
        return response()->json($riesgos);
    }

    public function show($id)
    {
        $riesgo = Riesgo::with(['controles', 'indicadores', 'actividades', 'seguimientos_mensuales', 'evaluaciones_trimestrales'])->findOrFail($id);
        return response()->json($riesgo);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'objetivo' => 'nullable|string',
            'efectos_consecuencias' => 'nullable|string',
            'riesgo' => 'required|string',
            'factores' => 'nullable|string',
            'factores_internos' => 'nullable|string',
            'factores_externos' => 'nullable|string',
            'probabilidad' => 'nullable|integer',
            'impacto' => 'nullable|integer',
            'probabilidad_inicial' => 'nullable|integer',
            'impacto_inicial' => 'nullable|integer',
            'status' => 'nullable|string',
            'actividades' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            \Log::info("Starting store...");

            // Generar local_id consecutivo por proyecto (area_id)
            $conteoExistente = Riesgo::where('area_id', $request->area_id)
                ->where('ejercicio_id', $request->ejercicio_id)
                ->count();
            $localId = 'R' . ($conteoExistente + 1);

            $datos = $request->all();
            $datos['local_id'] = $localId;

            $riesgo = Riesgo::create($datos);
            \Log::info("Riesgo created. ID: " . $riesgo->id . " local_id: " . $localId);

            if ($request->has('controles') && is_array($request->controles)) {
                foreach ($request->controles as $controlData) {
                    $riesgo->controles()->create($controlData);
                }
                \Log::info("Controles created.");
            }

            if ($request->has('indicadores') && is_array($request->indicadores)) {
                foreach ($request->indicadores as $indicadorData) {
                    $riesgo->indicadores()->create($indicadorData);
                }
            }

            if ($request->has('actividades') && is_array($request->actividades)) {
                \Log::info("Syncing actividades (manual to avoid cross-connection deadlock)...");
                \Illuminate\Support\Facades\DB::table('actividad_riesgo')->where('riesgo_id', $riesgo->id)->delete();
                $insertData = [];
                foreach ($request->actividades as $act_id) {
                    $insertData[] = [
                        'riesgo_id' => $riesgo->id,
                        'actividad_sustantiva_id' => $act_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (!empty($insertData)) {
                    \Illuminate\Support\Facades\DB::table('actividad_riesgo')->insert($insertData);
                }
                \Log::info("Actividades synced.");
            }

            DB::commit();
            \Log::info("Committed.");
            
            $riesgoLoaded = $riesgo->load(['controles', 'indicadores', 'actividades']);
            $this->syncPonencias($riesgoLoaded, $request);
            
            return response()->json($riesgoLoaded, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in store: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $riesgo = Riesgo::findOrFail($id);

        DB::beginTransaction();
        try {
            $riesgo->update($request->all());

            if ($request->has('controles') && is_array($request->controles)) {
                $riesgo->controles()->delete();
                foreach ($request->controles as $controlData) {
                    $riesgo->controles()->create($controlData);
                }
            }

            if ($request->has('indicadores') && is_array($request->indicadores)) {
                $riesgo->indicadores()->delete();
                foreach ($request->indicadores as $indicadorData) {
                    $riesgo->indicadores()->create($indicadorData);
                }
            }

            if ($request->has('actividades') && is_array($request->actividades)) {
                \Illuminate\Support\Facades\DB::table('actividad_riesgo')->where('riesgo_id', $riesgo->id)->delete();
                $insertData = [];
                foreach ($request->actividades as $act_id) {
                    $insertData[] = [
                        'riesgo_id' => $riesgo->id,
                        'actividad_sustantiva_id' => $act_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (!empty($insertData)) {
                    \Illuminate\Support\Facades\DB::table('actividad_riesgo')->insert($insertData);
                }
            }

            DB::commit();
            
            $riesgoLoaded = $riesgo->load(['controles', 'indicadores', 'actividades']);
            $this->syncPonencias($riesgoLoaded, $request);
            
            return response()->json($riesgoLoaded);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $riesgo = Riesgo::findOrFail($id);
        $riesgo->delete();
        return response()->json(null, 204);
    }

    public function validarControl(Request $request, $riesgoId, $controlId)
    {
        $request->validate([
            'estado_validacion' => 'required|string|in:Propuesto – pendiente de validación,Validado por el área'
        ]);

        $riesgo = Riesgo::findOrFail($riesgoId);
        $control = $riesgo->controles()->findOrFail($controlId);

        if ($request->estado_validacion === 'Validado por el área' && empty($control->evidencia_tipo) && empty($control->evidencia_referencia)) {
            return response()->json(['message' => 'Para validar el control registra primero evidencia o referencia verificable.'], 400);
        }

        $control->estado_validacion = $request->estado_validacion;
        $control->save();

        return response()->json(['message' => 'Control actualizado correctamente', 'control' => $control]);
    }

    public function batchValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'risk_ids' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            Riesgo::whereIn('id', $request->risk_ids)
                ->where('area_id', $request->area_id)
                ->where('ejercicio_id', $request->ejercicio_id)
                ->update(['status' => 'Validado']);
            
            DB::commit();
            return response()->json(['message' => 'Riesgos validados correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function syncPonencias(Riesgo $riesgo, Request $request)
    {
        $ponencias = ['PAAH', 'PJHR', 'POVR', 'PKSL', 'PLPJC'];
        
        if (!in_array($riesgo->area_id, $ponencias)) {
            return;
        }

        // Extract the R part from local_id, e.g. PAAH-2026-R1 -> R1
        $parts = explode('-', $riesgo->local_id);
        $suffix = end($parts);
        if (empty($suffix) || $suffix[0] !== 'R') {
            return;
        }

        foreach ($ponencias as $ponenciaArea) {
            if ($ponenciaArea === $riesgo->area_id) continue;

            $expectedLocalId = $ponenciaArea . '-' . $riesgo->ejercicio_id . '-' . $suffix;
            $peer = Riesgo::where('local_id', $expectedLocalId)
                          ->where('ejercicio_id', $riesgo->ejercicio_id)
                          ->first();

            if ($peer) {
                // Copy fields
                $peer->update([
                    'objetivo' => $riesgo->objetivo,
                    'riesgo' => $riesgo->riesgo,
                    'probabilidad' => $riesgo->probabilidad,
                    'impacto' => $riesgo->impacto,
                    'factores_internos' => $riesgo->factores_internos,
                    'factores_externos' => $riesgo->factores_externos,
                ]);

                // Sync controls
                if ($request->has('controles') && is_array($request->controles)) {
                    $peer->controles()->delete();
                    foreach ($request->controles as $c) {
                        $newC = $c;
                        unset($newC['id']); // Let DB auto-increment
                        unset($newC['riesgo_id']);
                        $peer->controles()->create($newC);
                    }
                }

                // Sync indicators
                if ($request->has('indicadores') && is_array($request->indicadores)) {
                    $peer->indicadores()->delete();
                    foreach ($request->indicadores as $i) {
                        $newI = $i;
                        unset($newI['id']);
                        unset($newI['riesgo_id']);
                        $peer->indicadores()->create($newI);
                    }
                }
            }
        }
    }
}
