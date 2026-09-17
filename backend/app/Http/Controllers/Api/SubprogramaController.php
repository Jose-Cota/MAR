<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubprogramaController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        if (!$ejercicio) return response()->json([]);

        $query = "
            SELECT 
                sp.numero,
                sp.nombre,
                sp.subprograma_id,
                pg.programa_id,
                pg.numero as programa_numero,
                pg.nombre as programa_nombre
            FROM subprogramas as sp
            JOIN programas as pg ON sp.programa_id = pg.programa_id
            JOIN ejercicios as ej ON pg.ejercicio_id = ej.ejercicio_id
            WHERE ej.ejercicio = ?
            ORDER BY pg.numero, sp.numero
        ";
        $data = DB::connection('poa_prod')->select($query, [$ejercicio]);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'programa_id' => 'required|integer',
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        $programa = DB::connection('poa_prod')->table('programas')->where('programa_id', $request->programa_id)->first();

        $id = DB::connection('poa_prod')->table('subprogramas')->insertGetId([
            'programa_id' => $request->programa_id,
            'ejercicio_id' => $programa ? $programa->ejercicio_id : null,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Subprograma creado', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'programa_id' => 'required|integer',
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        DB::connection('poa_prod')->table('subprogramas')->where('subprograma_id', $id)->update([
            'programa_id' => $request->programa_id,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Subprograma actualizado']);
    }

    public function destroy($id)
    {
        // Verificar dependencias
        $count = DB::connection('poa_prod')->table('proyectos')->where('subprograma_id', $id)->count();
        if ($count > 0) {
            return response()->json(['message' => 'No se puede eliminar porque tiene proyectos asociados'], 422);
        }

        $sp = DB::connection('poa_prod')->table('subprogramas as s')
            ->join('programas as p', 's.programa_id', '=', 'p.programa_id')
            ->where('s.subprograma_id', $id)
            ->select('s.*', 'p.ejercicio_id')
            ->first();

        if ($sp) {
            DB::connection('poa_prod')->table('subprogramas')->where('subprograma_id', $id)->delete();
            
            // Recorrer números de los subprogramas restantes del mismo ejercicio (global)
            $sps = DB::connection('poa_prod')->table('subprogramas as s')
                ->join('programas as p', 's.programa_id', '=', 'p.programa_id')
                ->where('p.ejercicio_id', $sp->ejercicio_id)
                ->where('s.numero', '>', $sp->numero)
                ->orderBy('s.numero', 'asc')
                ->select('s.subprograma_id', 's.numero')
                ->get();
                
            foreach ($sps as $s) {
                $nuevoNumero = intval($s->numero) - 1;
                DB::connection('poa_prod')->table('subprogramas')
                    ->where('subprograma_id', $s->subprograma_id)
                    ->update(['numero' => str_pad($nuevoNumero, 2, '0', STR_PAD_LEFT)]);
            }
        }

        return response()->json(['message' => 'Subprograma eliminado y numeración actualizada']);
    }
}
