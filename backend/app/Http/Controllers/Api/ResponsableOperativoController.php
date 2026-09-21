<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResponsableOperativoController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        $urgIds = $request->query('unidad_responsable_gasto_id');
        $user = $request->user();

        $isAdministradorGlobal = $user && ($user->hasRole('Super Administrador') || $user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->hasRole('DPyRF') || $user->roles->pluck('name')->contains('Administrador') || $user->roles->pluck('name')->contains('DPyRF') || $user->roles->pluck('name')->contains('Super Administrador'));

        $query = DB::connection('poa_prod')
            ->table('responsables_operativos as ro')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select(
                'ro.responsable_operativo_id',
                'ro.numero as ronum',
                'ro.numero',
                'ro.nombre as ronom',
                'ro.nombre',
                'ro.unidad_responsable_gasto_id',
                'urg.numero as urnum',
                'urg.numero as urg_numero'
            )
            ->orderBy('urg.numero')
            ->orderBy('ro.numero');

        if ($urgIds) {
            $urgIdsArray = is_string($urgIds) ? explode(',', $urgIds) : (is_array($urgIds) ? $urgIds : [$urgIds]);
            $query->whereIn('ro.unidad_responsable_gasto_id', $urgIdsArray);
        } else if ($ejercicio) {
            $query->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
                  ->where('ej.ejercicio', $ejercicio);
        } else {
            return response()->json([]);
        }

        if (!$isAdministradorGlobal) {
            // Check specific RO assignments
            $query->whereExists(function($subquery) use ($user) {
                $subquery->select(DB::raw(1))
                         ->from('usuarios_responsables_operativos as uro')
                         ->whereColumn('ro.responsable_operativo_id', 'uro.responsable_operativo_id') 
                         ->where('uro.usuario_poa_id', $user->usuario_poa_id);
            });
        }

        $data = $query->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unidad_responsable_gasto_id' => 'required|integer',
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $request->unidad_responsable_gasto_id)->first();

        $id = DB::connection('poa_prod')->table('responsables_operativos')->insertGetId([
            'unidad_responsable_gasto_id' => $request->unidad_responsable_gasto_id,
            'ejercicio_id' => $urg ? $urg->ejercicio_id : null,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Responsable Operativo creado', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unidad_responsable_gasto_id' => 'required|integer',
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        DB::connection('poa_prod')->table('responsables_operativos')->where('responsable_operativo_id', $id)->update([
            'unidad_responsable_gasto_id' => $request->unidad_responsable_gasto_id,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Responsable Operativo actualizado']);
    }

    public function destroy($id)
    {
        $count = DB::connection('poa_prod')->table('proyectos')->where('responsable_operativo_id', $id)->count();

        if ($count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar porque tiene proyectos asociados.'
            ], 422);
        }

        DB::connection('poa_prod')->beginTransaction();
        try {
            $ro = DB::connection('poa_prod')->table('responsables_operativos')->where('responsable_operativo_id', $id)->first();
            if ($ro) {
                DB::connection('poa_prod')->table('responsables_operativos')->where('responsable_operativo_id', $id)->delete();
                
                // Recorrer números de los RO restantes de la misma URG
                $ros = DB::connection('poa_prod')->table('responsables_operativos')
                    ->where('unidad_responsable_gasto_id', $ro->unidad_responsable_gasto_id)
                    ->where('numero', '>', $ro->numero)
                    ->orderBy('numero', 'asc')
                    ->get();
                    
                foreach ($ros as $r) {
                    $nuevoNumero = intval($r->numero) - 1;
                    DB::connection('poa_prod')->table('responsables_operativos')
                        ->where('responsable_operativo_id', $r->responsable_operativo_id)
                        ->update(['numero' => str_pad($nuevoNumero, 2, '0', STR_PAD_LEFT)]);
                }
            }
            
            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Responsable Operativo eliminado correctamente']);
        } catch (\Exception $e) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'Error al eliminar el Responsable Operativo', 'error' => $e->getMessage()], 500);
        }
    }
}
