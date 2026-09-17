<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramaController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        if (!$ejercicio) return response()->json([]);

        $query = "
            SELECT 
                pg.numero,
                pg.nombre,
                pg.programa_id
            FROM programas as pg
            JOIN ejercicios as ej ON pg.ejercicio_id = ej.ejercicio_id
            WHERE ej.ejercicio = ?
            ORDER BY pg.numero
        ";
        $data = DB::connection('poa_prod')->select($query, [$ejercicio]);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ejercicio' => 'required|integer',
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $request->ejercicio)->first();
        if (!$ejercicio) return response()->json(['message' => 'Ejercicio no encontrado'], 404);

        $id = DB::connection('poa_prod')->table('programas')->insertGetId([
            'ejercicio_id' => $ejercicio->ejercicio_id,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Programa creado', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero' => 'required|string|max:10',
            'nombre' => 'required|string|max:512',
        ]);

        DB::connection('poa_prod')->table('programas')->where('programa_id', $id)->update([
            'numero' => $request->numero,
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Programa actualizado']);
    }

    public function destroy($id)
    {
        // Verificar si tiene subprogramas dependientes
        $count = DB::connection('poa_prod')->table('subprogramas')->where('programa_id', $id)->count();
        if ($count > 0) {
            return response()->json(['message' => 'No se puede eliminar porque tiene subprogramas asociados'], 422);
        }

        DB::connection('poa_prod')->table('programas')->where('programa_id', $id)->delete();
        return response()->json(['message' => 'Programa eliminado']);
    }
}
