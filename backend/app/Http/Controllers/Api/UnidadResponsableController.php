<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UnidadResponsableController extends Controller
{
    /**
     * Get all unidades responsables
     */
    public function index(Request $request)
    {
        $filtrarPorEjercicio = $request->has('ejercicio') && !empty($request->ejercicio);

        if ($filtrarPorEjercicio) {
            $query = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos as urg')
                ->join('ejercicios as e', 'urg.ejercicio_id', '=', 'e.ejercicio_id')
                ->select('urg.*')
                ->where('e.ejercicio', $request->ejercicio)
                ->orderBy('urg.numero');
        } else {
            // Sin ejercicio: tomar solo el registro más reciente por número de URG
            $subquery = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos')
                ->select('numero', DB::raw('MAX(ejercicio_id) as max_ejercicio_id'))
                ->groupBy('numero');

            $query = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos as urg')
                ->joinSub($subquery, 'latest', function ($join) {
                    $join->on('urg.numero', '=', 'latest.numero')
                         ->on('urg.ejercicio_id', '=', 'latest.max_ejercicio_id');
                })
                ->select('urg.*')
                ->orderBy('urg.numero');
        }

        $isAdministradorGlobal = $request->user()->hasRole('Super Administrador')
            || $request->user()->hasRole('Administrador')
            || $request->user()->hasRole('DPyRF')
            || $request->user()->roles->pluck('name')->contains('Administrador')
            || $request->user()->roles->pluck('name')->contains('DPyRF')
            || $request->user()->roles->pluck('name')->contains('Super Administrador');

        if (!$isAdministradorGlobal) {
            $user = $request->user();

            // 1. Intentar obtener números de URG desde la tabla pivote (filtrar id=0 que son inválidos)
            $urgIds = \Illuminate\Support\Facades\DB::table('usuario_unidad_responsable')
                ->where('usuario_poa_id', $user->usuario_poa_id)
                ->where('unidad_responsable_gasto_id', '>', 0)
                ->pluck('unidad_responsable_gasto_id')
                ->toArray();

            $userUrgNumbers = [];

            if (!empty($urgIds)) {
                $userUrgNumbers = \Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')
                    ->whereIn('unidad_responsable_gasto_id', $urgIds)
                    ->pluck('numero')
                    ->toArray();
            }

            // 2. Fallback: usar area_id del usuario
            if (empty($userUrgNumbers) && $user->area_id) {
                $userUrgNumbers = \Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')
                    ->where('unidad_responsable_gasto_id', $user->area_id)
                    ->pluck('numero')
                    ->toArray();
            }

            // 3. Si encontramos números de URG, filtrar
            if (!empty($userUrgNumbers)) {
                $query->whereIn('urg.numero', $userUrgNumbers);
            }
            // Si no hay info de URG para el usuario, devolvemos todo (para no bloquear)
        }

        $unidades = $query->get();

        return response()->json($unidades);

    }


    /**
     * Store a newly created unidad responsable
     */
    public function store(Request $request)
    {
        // ... (store logic remains same, but we could add an authorization check here)
        if (!$request->user()->hasRole('Super Administrador') && !$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:5',
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if numero already exists
        $exists = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->where('numero', $request->numero)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El número de unidad ya existe'], 400);
        }

        $id = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->insertGetId([
                'ejercicio_id' => 1, // Defaulting to 1 for now, similar to old system session exercise
                'numero' => $request->numero,
                'nombre' => $request->nombre,
                'cerrada' => 0 // Default to open
            ]);

        $unidad = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->first();

        return response()->json($unidad, 201);
    }

    /**
     * Update the specified unidad responsable
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $updateData = [];

        if (!$user->hasRole('Administrador')) {
            $userUrgIds = $user->unidadesResponsables->pluck('unidad_responsable_gasto_id')->toArray();
            if (empty($userUrgIds)) {
                $userUrgIds = [$user->area_id];
            }
            if (!in_array($id, $userUrgIds)) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'numero' => 'required|string|max:5',
                'nombre' => 'required|string|max:255',
                'cerrada' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if numero exists for another unit
            $exists = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos')
                ->where('numero', $request->numero)
                ->where('unidad_responsable_gasto_id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'El número de unidad ya está en uso'], 400);
            }

            $updateData['numero'] = $request->numero;
            $updateData['nombre'] = $request->nombre;
        }

        // If cerrada is sent, update it
        if ($request->has('cerrada')) {
            $updateData['cerrada'] = $request->cerrada;
            
            // Sync with g_registros if needed as per old logic
            DB::connection('poa_prod')
                ->table('g_registros')
                ->where('area_id', $id)
                ->update(['cerrado' => $request->cerrada]);
        }

        if (!empty($updateData)) {
            DB::connection('poa_prod')
                ->table('unidades_responsables_gastos')
                ->where('unidad_responsable_gasto_id', $id)
                ->update($updateData);
        }

        $unidad = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->first();

        return response()->json($unidad);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasRole('Administrador')) {
            $userUrgIds = $user->unidadesResponsables->pluck('unidad_responsable_gasto_id')->toArray();
            if (empty($userUrgIds)) {
                $userUrgIds = [$user->area_id];
            }
            if (!in_array($id, $userUrgIds)) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        }

        DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->delete();

        return response()->json(null, 204);
    }
}
