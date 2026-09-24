<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadSustantivaController extends Controller
{
    public function index(Request $request)
    {
        $area_id = $request->input('area_id');
        $query = DB::table('actividades_sustantivas')
            ->join('proyectos', 'actividades_sustantivas.proyecto_id', '=', 'proyectos.proyecto_id')
            ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
            ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id');

        if ($area_id && $area_id !== 'undefined') {
            $query->where('responsables_operativos.unidad_responsable_gasto_id', $area_id);
        }

        $actividades = $query->select(
            'actividades_sustantivas.*', 
            'proyectos.nombre as proyecto_nombre',
            'proyectos.numero as py_num',
            'subprogramas.numero as sp_num',
            'programas.numero as pg_num',
            'responsables_operativos.numero as ro_num',
            'unidades_responsables_gastos.numero as urg_num',
            'unidades_responsables_gastos.unidad_responsable_gasto_id as area_id'
        )->get();
        return response()->json($actividades);
    }
    private function getTableForProyecto($proyectoId) {
        return 'actividades_sustantivas';
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proyecto_id' => 'required|integer',
            'numero' => 'nullable|integer',
            'descripcion' => 'required|string',
            'recursos_asociados' => 'nullable|string',
        ]);

        $table = $this->getTableForProyecto($validated['proyecto_id']);

        if (empty($validated['numero'])) {
            $maxNum = DB::connection('poa_prod')->table($table)
                ->where('proyecto_id', $validated['proyecto_id'])
                ->max('numero');
            $validated['numero'] = $maxNum ? $maxNum + 1 : 1;
        }

        $id = DB::connection('poa_prod')->table($table)->insertGetId([
            'proyecto_id' => $validated['proyecto_id'],
            'numero' => $validated['numero'],
            'descripcion' => $validated['descripcion'],
            'recursos_asociados' => $validated['recursos_asociados'],
        ]);

        $pk = 'id';

        return response()->json([
            'id' => $id,
            $pk => $id,
            'proyecto_id' => $validated['proyecto_id'],
            'numero' => $validated['numero'],
            'descripcion' => $validated['descripcion'],
            'recursos_asociados' => $validated['recursos_asociados'],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'numero' => 'nullable|integer',
            'descripcion' => 'required|string',
            'recursos_asociados' => 'nullable|string',
        ]);

        $table = $this->getTableForActividad($id);
        $pk = 'id';

        $actividad = DB::connection('poa_prod')->table($table)->where($pk, $id)->first();
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        DB::connection('poa_prod')->table($table)->where($pk, $id)->update($validated);

        return response()->json(DB::connection('poa_prod')->table($table)->where($pk, $id)->first());
    }

    public function destroy($id)
    {
        $table = $this->getTableForActividad($id);
        $pk = 'id';

        $actividad = DB::connection('poa_prod')->table($table)->where($pk, $id)->first();
        if (!$actividad) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        DB::connection('poa_prod')->table($table)->where($pk, $id)->delete();

        return response()->json(null, 204);
    }
}
