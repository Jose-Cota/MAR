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

        // Las URG estructurales POA (id >= 240) se usan para conectar los proyectos
        // en getFichas, pero NO deben mostrarse en el listado/dropdown (duplicarían
        // las URG del catálogo 1-25 con el mismo nombre).
        if ($filtrarPorEjercicio) {
            $query = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos as urg')
                ->join('ejercicios as e', 'urg.ejercicio_id', '=', 'e.ejercicio_id')
                ->select('urg.*')
                ->where('e.ejercicio', $request->ejercicio)
                ->where('urg.unidad_responsable_gasto_id', '<', 240)
                ->orderBy('urg.numero');
        } else {
            // Sin ejercicio: tomar solo el registro más reciente por número de URG
            $subquery = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos')
                ->where('unidad_responsable_gasto_id', '<', 240)
                ->select('numero', DB::raw('MAX(ejercicio_id) as max_ejercicio_id'))
                ->groupBy('numero');

            $query = DB::connection('poa_prod')
                ->table('unidades_responsables_gastos as urg')
                ->joinSub($subquery, 'latest', function ($join) {
                    $join->on('urg.numero', '=', 'latest.numero')
                         ->on('urg.ejercicio_id', '=', 'latest.max_ejercicio_id');
                })
                ->select('urg.*')
                ->where('urg.unidad_responsable_gasto_id', '<', 240)
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
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'titular' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Determinar ID para guardar
        $maxId = DB::table('unidades_responsables_gastos')
            ->max('unidad_responsable_gasto_id');
        
        $newId = $maxId ? $maxId + 1 : 1;

        DB::table('unidades_responsables_gastos')->insert([
            'unidad_responsable_gasto_id' => $newId,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'titular' => $request->titular,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nuevaUnidad = DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $newId)
            ->first();

        return response()->json($nuevaUnidad, 201);
    }

    /**
     * Update the specified unidad responsable
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'titular' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unidad = DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->first();

        if (!$unidad) {
            return response()->json(['message' => 'Unidad no encontrada'], 404);
        }

        DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'titular' => $request->titular,
                'updated_at' => now(),
            ]);

        $unidadActualizada = DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->first();

        return response()->json($unidadActualizada);
    }

    /**
     * Remove the specified unidad responsable
     */
    public function destroy($id)
    {
        $unidad = DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->first();

        if (!$unidad) {
            return response()->json(['message' => 'Unidad no encontrada'], 404);
        }

        DB::table('unidades_responsables_gastos')
            ->where('unidad_responsable_gasto_id', $id)
            ->delete();

        return response()->json(['message' => 'Unidad eliminada exitosamente']);
    }
}
