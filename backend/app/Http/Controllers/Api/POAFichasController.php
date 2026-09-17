<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proyecto;

class POAFichasController extends Controller
{
    public function getFichas(Request $request)
    {
        $ejercicio_id = $request->input('ejercicio_id', date('Y'));
        $area_id = $request->input('area_id');

        if (!$area_id || $area_id === 'undefined') {
            return response()->json([]);
        }

        // Fetch projects (proyectos)
        $proyectos = DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->where('proyectos.ejercicio_id', $ejercicio_id)
            ->where('responsables_operativos.unidad_responsable_gasto_id', $area_id)
            ->select('proyectos.*', 'proyectos.proyecto_id as id') // Ensure id is accessible
            ->get();

        foreach ($proyectos as $p) {
            // Fetch goals (metas)
            $p->metas = DB::table('metas')
                ->where('id_proyecto', $p->id)
                ->get();
                
            // Fetch indicators (indicadores)
            $p->indicadores = DB::table('indicadores')
                ->where('id_proyecto', $p->id)
                ->get();

            // Fetch actions (actividades_sustantivas)
            $p->acciones = DB::table('actividades_sustantivas')
                ->where('proyecto_id', $p->id)
                ->get();

            // Find linked risks for actions
            foreach ($p->acciones as $accion) {
                $accion->riesgos_vinculados = DB::table('actividad_riesgo')
                    ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
                    ->where('actividad_riesgo.actividad_sustantiva_id', $accion->id)
                    ->select('riesgos.id', 'riesgos.local_id', 'riesgos.riesgo')
                    ->get();
            }
        }

        return response()->json($proyectos);
    }
}
