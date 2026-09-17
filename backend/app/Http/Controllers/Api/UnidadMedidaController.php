<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadMedidaController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        if (!$ejercicio) return response()->json([]);

        $query = "
            SELECT 
                um.unidad_medida_id,
                um.numero,
                um.nombre,
                um.descripcion,
                um.porcentajes,
                EXISTS (SELECT 1 FROM metas m WHERE m.unidad_medida_id = um.unidad_medida_id) OR
                EXISTS (SELECT 1 FROM indicadores i WHERE i.unidad_medida_id = um.unidad_medida_id) as utilizado
            FROM unidades_medidas as um
            JOIN ejercicios as ej ON um.ejercicio_id = ej.ejercicio_id
            WHERE ej.ejercicio = ?
            ORDER BY um.numero
        ";
        $data = DB::connection('poa_prod')->select($query, [$ejercicio]);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ejercicio' => 'required|integer',
            'numero' => 'nullable|string|max:10',
            'nombre' => 'required|string|max:512',
            'descripcion' => 'nullable|string',
            'porcentajes' => 'nullable|boolean',
        ]);

        $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $request->ejercicio)->first();
        if (!$ejercicio) return response()->json(['message' => 'Ejercicio no encontrado'], 404);

        $numero = $request->numero;
        if (empty($numero)) {
            $maxNum = DB::connection('poa_prod')->table('unidades_medidas')
                ->where('ejercicio_id', $ejercicio->ejercicio_id)
                ->orderByRaw('CAST(numero AS UNSIGNED) DESC')
                ->value('numero');
            $nextNum = $maxNum ? ((int)$maxNum) + 1 : 1;
            $len = $maxNum ? strlen($maxNum) : 2;
            if ($len < 2) $len = 2;
            $numero = str_pad($nextNum, $len, '0', STR_PAD_LEFT);
        }

        $id = DB::connection('poa_prod')->table('unidades_medidas')->insertGetId([
            'ejercicio_id' => $ejercicio->ejercicio_id,
            'numero' => $numero,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ?? '',
            'porcentajes' => $request->porcentajes ? 1 : 0,
        ]);

        return response()->json(['message' => 'Unidad de medida creada', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
            'descripcion' => 'nullable|string',
            'porcentajes' => 'boolean',
        ]);

        DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', $id)->update([
            'numero' => $request->numero,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion ?? '',
            'porcentajes' => $request->porcentajes ? 1 : 0,
        ]);

        return response()->json(['message' => 'Unidad de medida actualizada']);
    }

    public function destroy($id)
    {
        $count = DB::connection('poa_prod')->table('metas')->where('unidad_medida_id', $id)->count();
        if ($count > 0) {
            return response()->json(['message' => 'No se puede eliminar porque tiene metas asociadas'], 422);
        }

        DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', $id)->delete();
        return response()->json(['message' => 'Unidad de medida eliminada']);
    }

    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['message' => 'No hay IDs seleccionados'], 422);

        // Find units that have metas
        $usedIds = DB::connection('poa_prod')->table('metas')
            ->whereIn('unidad_medida_id', $ids)
            ->pluck('unidad_medida_id')
            ->toArray();
        
        $usedIdsInds = DB::connection('poa_prod')->table('indicadores')
            ->whereIn('unidad_medida_id', $ids)
            ->pluck('unidad_medida_id')
            ->toArray();

        $allUsed = array_unique(array_merge($usedIds, $usedIdsInds));
        $toDelete = array_diff($ids, $allUsed);

        if (!empty($toDelete)) {
            DB::connection('poa_prod')->table('unidades_medidas')->whereIn('unidad_medida_id', $toDelete)->delete();
        }

        return response()->json([
            'message' => 'Eliminación procesada',
            'deleted' => count($toDelete),
            'skipped' => count($ids) - count($toDelete)
        ]);
    }

    public function getDuplicates(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        $idsStr = $request->query('ids');
        if (!$ejercicio || !$idsStr) return response()->json([]);

        $ids = explode(',', $idsStr);
        if (empty($ids)) return response()->json([]);

        // Clean names for matching: remove %, accents, lower case.
        // We match where d.unidad_medida_id IN ($ids) and o.unidad_medida_id NOT IN ($ids)
        // and they belong to the same ejercicio.
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $bindings = array_merge([$ejercicio], $ids, $ids);

        $query = "
            SELECT 
                o.unidad_medida_id as original_id,
                o.nombre as original_nombre,
                o.descripcion as original_descripcion,
                d.unidad_medida_id as duplicado_id,
                d.nombre as duplicado_nombre,
                d.descripcion as duplicado_descripcion,
                (
                    (SELECT COUNT(*) FROM metas WHERE unidad_medida_id = d.unidad_medida_id) +
                    (SELECT COUNT(*) FROM indicadores WHERE unidad_medida_id = d.unidad_medida_id) +
                    (SELECT COUNT(*) FROM unidades_medidas_derechos_humanos WHERE unidad_medida_id = d.unidad_medida_id)
                ) as duplicado_usos
            FROM unidades_medidas d
            JOIN unidades_medidas o ON d.ejercicio_id = o.ejercicio_id
            JOIN ejercicios as ej ON d.ejercicio_id = ej.ejercicio_id
            WHERE ej.ejercicio = ?
              AND d.unidad_medida_id IN ($placeholders)
              AND d.unidad_medida_id != o.unidad_medida_id
              AND (
                  REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(REPLACE(d.nombre, '%', '')), 'á','a'), 'é','e'), 'í','i'), 'ó','o'), 'ú','u') 
                  = 
                  REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(REPLACE(o.nombre, '%', '')), 'á','a'), 'é','e'), 'í','i'), 'ó','o'), 'ú','u')
              )
              AND (
                  o.unidad_medida_id NOT IN ($placeholders)
                  OR 
                  (d.nombre LIKE '\%%' AND o.nombre NOT LIKE '\%%')
                  OR 
                  (d.nombre NOT LIKE '\%%' AND o.nombre NOT LIKE '\%%' AND d.unidad_medida_id > o.unidad_medida_id)
                  OR 
                  (d.nombre LIKE '\%%' AND o.nombre LIKE '\%%' AND d.unidad_medida_id > o.unidad_medida_id)
              )
        ";
        $data = DB::connection('poa_prod')->select($query, $bindings);
        return response()->json($data);
    }

    public function migrate(Request $request)
    {
        $request->validate([
            'original_id' => 'required|integer',
            'duplicado_id' => 'required|integer'
        ]);

        $originalId = $request->input('original_id');
        $duplicadoId = $request->input('duplicado_id');

        DB::connection('poa_prod')->transaction(function () use ($originalId, $duplicadoId) {
            DB::connection('poa_prod')->table('metas')
                ->where('unidad_medida_id', $duplicadoId)
                ->update(['unidad_medida_id' => $originalId]);
            
            DB::connection('poa_prod')->table('indicadores')
                ->where('unidad_medida_id', $duplicadoId)
                ->update(['unidad_medida_id' => $originalId]);

            DB::connection('poa_prod')->table('unidades_medidas_derechos_humanos')
                ->where('unidad_medida_id', $duplicadoId)
                ->update(['unidad_medida_id' => $originalId]);
        });

        return response()->json(['message' => 'Migración exitosa, referencias actualizadas']);
    }
}
