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
        $query = Riesgo::with(['controles', 'indicadores', 'actividades']);

        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->has('ejercicio_id')) {
            $query->where('ejercicio_id', $request->ejercicio_id);
        }

        $riesgos = $query->get();
        return response()->json($riesgos);
    }

    public function show($id)
    {
        $riesgo = Riesgo::with(['controles', 'indicadores', 'actividades'])->findOrFail($id);
        return response()->json($riesgo);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'objetivo' => 'nullable|string',
            'riesgo' => 'required|string',
            'factores' => 'nullable|string',
            'probabilidad' => 'nullable|integer',
            'impacto' => 'nullable|integer',
            'probabilidad_inicial' => 'nullable|integer',
            'impacto_inicial' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            \Log::info("Starting store...");
            $riesgo = Riesgo::create($request->all());
            \Log::info("Riesgo created. ID: " . $riesgo->id);

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
            return response()->json($riesgo->load(['controles', 'indicadores', 'actividades']), 201);
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
            return response()->json($riesgo->load(['controles', 'indicadores', 'actividades']));
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
}
