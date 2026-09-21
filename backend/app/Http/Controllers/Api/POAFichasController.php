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

        // El frontend envía el año (ej: 2026), pero la DB usa una clave foránea.
        // Si el valor no existe directamente en proyectos, resolverlo desde la tabla ejercicios.
        $ejercicioRow = DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
        $ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;

        // Resolver el número de la URG para el area_id recibido
        $urg = DB::table('unidades_responsables_gasto')->where('unidad_responsable_gasto_id', $area_id)->first();
        $urg_numero = $urg ? $urg->numero : $area_id;

        // Fetch projects (proyectos)
        $query = DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->join('unidades_responsables_gasto', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gasto.unidad_responsable_gasto_id')
            ->where('proyectos.ejercicio_id', $ejercicio_db_id)
            ->select('proyectos.*', 'proyectos.proyecto_id as id', 'responsables_operativos.unidad_responsable_gasto_id as urg_id');

        if ($area_id !== 'todas') {
            $query->where('unidades_responsables_gasto.numero', $urg_numero);
        }

        $proyectos = $query->get();

        foreach ($proyectos as $p) {
            // Fetch goals (metas)
            $p->metas = DB::table('metas')
                ->where('proyecto_id', $p->id)
                ->get();
                
            // Fetch indicators (indicadores)
            $p->indicadores = DB::table('indicadores')
                ->where('proyecto_id', $p->id)
                ->get();

            // Fetch actions — la tabla POA debe usar acciones_sustantivas de la UR
            $p->acciones = DB::table('acciones_sustantivas')
                ->where('proyecto_id', $p->id)
                ->get();

            // Find linked risks for actions via the MAR pivot table
            foreach ($p->acciones as $accion) {
                $accion->riesgos_vinculados = DB::table('actividad_riesgo')
                    ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
                    ->where('actividad_riesgo.actividad_sustantiva_id', $accion->accion_sustantiva_id)
                    ->select('riesgos.id', 'riesgos.local_id', 'riesgos.riesgo')
                    ->get();
            }
        }

        return response()->json($proyectos);
    }
}
