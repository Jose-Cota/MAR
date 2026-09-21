<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POAFichasController extends Controller
{
    public function getFichas(Request $request)
    {
        $ejercicio_id = $request->input('ejercicio_id', date('Y'));
        $area_id      = $request->input('area_id');

        if (!$area_id || $area_id === 'undefined') {
            return response()->json([]);
        }

        // Resolver ejercicio_id numérico desde el año (ej: 2027 → 19)
        $ejercicioRow    = DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
        $ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;

        // Obtener datos de la URG seleccionada (tabla principal, IDs 1-25)
        $urgSeleccionada = ($area_id !== 'todas')
            ? DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $area_id)->first()
            : null;

        // Obtener todos los ROs del ejercicio con su urg_id (poa)
        // y construir mapa: urg_id_poa → nombre_urg
        // El campo 'nombre' en responsables_operativos es el nombre del PUESTO (RO),
        // y el urg_id_poa (564-581) es el ID del área en la estructura POA.
        // Para mapear, necesitamos conocer qué urg_ids_poa corresponden al área seleccionada.
        //
        // Estrategia: buscar los urg_ids_poa cuyo 'nombre_urg' (primer RO del grupo)
        // contenga palabras clave del nombre de la URG seleccionada.
        
        $rgIds = null; // null = sin filtro (todas las áreas)

        if ($urgSeleccionada && $area_id !== 'todas') {
            // Obtener todos los urg_ids únicos de responsables_operativos para este ejercicio
            $rosEjercicio = DB::table('responsables_operativos')
                ->where('ejercicio_id', $ejercicio_db_id)
                ->get()
                ->groupBy('unidad_responsable_gasto_id');

            // Buscar qué urg_id_poa corresponde a la URG seleccionada.
            // Comparamos el nombre de la URG contra los nombres de los ROs del grupo
            // (un RO por área generalmente incluye el nombre del área en su nombre).
            $urgNombreLower = mb_strtolower(trim($urgSeleccionada->nombre));
            $urgNumero      = trim($urgSeleccionada->numero);

            $urgIdsPoa = [];
            foreach ($rosEjercicio as $urgIdPoa => $rosGrupo) {
                foreach ($rosGrupo as $ro) {
                    $roNombreLower = mb_strtolower(trim($ro->nombre));
                    // Match: el nombre del RO contiene palabras significativas del área
                    // Extraemos palabras > 5 chars del nombre del área para buscar
                    $palabras = array_filter(
                        explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $urgNombreLower)),
                        fn($p) => mb_strlen($p) > 5
                    );
                    $coincidencias = 0;
                    foreach ($palabras as $palabra) {
                        if (str_contains($roNombreLower, $palabra)) {
                            $coincidencias++;
                        }
                    }
                    if ($coincidencias >= 2 || (count($palabras) === 1 && $coincidencias >= 1)) {
                        $urgIdsPoa[] = (int) $urgIdPoa;
                        break;
                    }
                }
            }

            $rgIds = array_unique($urgIdsPoa);
        }

        // Obtener proyectos del ejercicio, con filtro opcional de RO
        $query = DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->where('proyectos.ejercicio_id', $ejercicio_db_id)
            ->select(
                'proyectos.*',
                'proyectos.proyecto_id as id',
                'responsables_operativos.unidad_responsable_gasto_id as urg_id',
                'responsables_operativos.nombre as ro_nombre'
            );

        if ($rgIds !== null) {
            if (empty($rgIds)) {
                // No se encontró coincidencia — devolver vacío
                return response()->json([]);
            }
            $query->whereIn('responsables_operativos.unidad_responsable_gasto_id', $rgIds);
        }

        $proyectos = $query->get();

        foreach ($proyectos as $p) {
            $p->metas = DB::table('metas')->where('proyecto_id', $p->id)->get();
            $p->indicadores = DB::table('indicadores')->where('proyecto_id', $p->id)->get();

            // Actividades sustantivas
            $actividades = DB::table('actividades_sustantivas')->where('proyecto_id', $p->id)->get();
            if ($actividades->isEmpty()) {
                $actividades = DB::table('acciones_sustantivas')->where('proyecto_id', $p->id)->get();
            }
            $p->acciones = $actividades;

            foreach ($p->acciones as $accion) {
                $actId = $accion->id ?? $accion->accion_sustantiva_id ?? null;
                $accion->riesgos_vinculados = $actId
                    ? DB::table('actividad_riesgo')
                        ->join('riesgos', 'actividad_riesgo.riesgo_id', '=', 'riesgos.id')
                        ->where('actividad_riesgo.actividad_sustantiva_id', $actId)
                        ->select('riesgos.id', 'riesgos.local_id', 'riesgos.riesgo')
                        ->get()
                    : collect();
            }
        }

        return response()->json($proyectos->values());
    }
}
