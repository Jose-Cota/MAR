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
                    // Normalizar acentos
                    $roNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($ro->nombre)));
                    $roNombreClean = preg_replace('/[éèëê]/u', 'e', $roNombreClean);
                    $roNombreClean = preg_replace('/[íìïî]/u', 'i', $roNombreClean);
                    $roNombreClean = preg_replace('/[óòöô]/u', 'o', $roNombreClean);
                    $roNombreClean = preg_replace('/[úùüû]/u', 'u', $roNombreClean);
                    
                    // Normalizaciones manuales de cargos a áreas
                    $roNombreClean = str_replace(['director', 'directora'], 'direccion', $roNombreClean);
                    $roNombreClean = str_replace(['presidente', 'presidenta'], 'presidencia', $roNombreClean);
                    $roNombreClean = str_replace(['secretario', 'secretaria'], 'secretaria', $roNombreClean);
                    $roNombreClean = str_replace(['contralor', 'contralora'], 'contraloria', $roNombreClean);
                    $roNombreClean = str_replace(['interno', 'interna'], 'interna', $roNombreClean);
                    $roNombreClean = str_replace(['defensor', 'defensora'], 'defensoria', $roNombreClean);
                    $roNombreClean = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $roNombreClean);
                    
                    $urgNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($urgSeleccionada->nombre)));
                    $urgNombreClean = preg_replace('/[éèëê]/u', 'e', $urgNombreClean);
                    $urgNombreClean = preg_replace('/[íìïî]/u', 'i', $urgNombreClean);
                    $urgNombreClean = preg_replace('/[óòöô]/u', 'o', $urgNombreClean);
                    $urgNombreClean = preg_replace('/[úùüû]/u', 'u', $urgNombreClean);

                    $palabras = array_filter(
                        explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $urgNombreClean)),
                        fn($p) => mb_strlen($p) > 5
                    );
                    $coincidencias = 0;
                    foreach ($palabras as $palabra) {
                        if (str_contains($roNombreClean, $palabra)) {
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

        // Construir mapa inverso de urg_id_poa -> URG ID (1-25)
        $mapa_ro_urg = [];
        $todasUrgs = DB::table('unidades_responsables_gastos')->get();
        $rosEjercicio = DB::table('responsables_operativos')->where('ejercicio_id', $ejercicio_db_id)->get()->groupBy('unidad_responsable_gasto_id');
        
        foreach ($todasUrgs as $u) {
            $uClean = preg_replace('/[áàäâéèëêíìïîóòöôúùüû]/u', 'a', mb_strtolower(trim($u->nombre)));
            $uClean = str_replace(['é','í','ó','ú'], ['e','i','o','u'], mb_strtolower(trim($u->nombre))); // Simple replace
            
            // just to be safe, use same manual normalizations
            $uClean = str_replace(['director', 'directora'], 'direccion', $uClean);
            $uClean = str_replace(['presidente', 'presidenta'], 'presidencia', $uClean);
            $uClean = str_replace(['secretario', 'secretaria'], 'secretaria', $uClean);
            $uClean = str_replace(['contralor', 'contralora'], 'contraloria', $uClean);
            $uClean = str_replace(['interno', 'interna'], 'interna', $uClean);
            $uClean = str_replace(['defensor', 'defensora'], 'defensoria', $uClean);
            $uClean = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $uClean);
            
            $palabras = array_filter(explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $uClean)), fn($p) => mb_strlen($p) > 5);
            
            foreach ($rosEjercicio as $urgIdPoa => $rosGrupo) {
                foreach ($rosGrupo as $ro) {
                    $rClean = mb_strtolower(trim($ro->nombre));
                    $rClean = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $rClean);
                    $rClean = str_replace(['director', 'directora'], 'direccion', $rClean);
                    $rClean = str_replace(['presidente', 'presidenta'], 'presidencia', $rClean);
                    $rClean = str_replace(['secretario', 'secretaria'], 'secretaria', $rClean);
                    $rClean = str_replace(['contralor', 'contralora'], 'contraloria', $rClean);
                    $rClean = str_replace(['interno', 'interna'], 'interna', $rClean);
                    $rClean = str_replace(['defensor', 'defensora'], 'defensoria', $rClean);
                    $rClean = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $rClean);

                    $coincidencias = 0;
                    foreach ($palabras as $palabra) {
                        if (str_contains($rClean, $palabra)) $coincidencias++;
                    }
                    if ($coincidencias >= 2 || (count($palabras) === 1 && $coincidencias >= 1)) {
                        $mapa_ro_urg[$urgIdPoa] = $u->unidad_responsable_gasto_id;
                        break;
                    }
                }
            }
        }

        foreach ($proyectos as $p) {
            $real_area_id = $mapa_ro_urg[$p->urg_id] ?? null;
            $p->riesgos_area = $real_area_id ? DB::table('riesgos')->where('ejercicio_id', $ejercicio_db_id)->where('area_id', $real_area_id)->select('id', 'local_id', 'riesgo')->get() : collect();
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
