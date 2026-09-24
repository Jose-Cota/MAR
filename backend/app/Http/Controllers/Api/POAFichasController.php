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

        // Umbral mínimo de IDs de URG estructurales (POA): 2026 usa 240-257 y 2027 usa 564-581.
        $urgEstructuralMin = 240;

        if ($urgSeleccionada && $area_id !== 'todas') {
            // Mapeo determinístico de la URG seleccionada hacia los responsables_operativos
            // del ejercicio, sin coincidencias por semejanza de texto:
            //  1) Si la URG seleccionada es una URG estructural POA (id >= 240),
            //     sus RO son todos los responsables_operativos con ese urg_id.
            //  2) Si es una URG del catálogo 1-25, buscamos la URG estructural POA
            //     (id >= 240) del mismo ejercicio con el MISMO nombre (normalizado sin
            //     acentos) y usamos sus RO. Fallback: RO cuyo nombre sea idéntico al
            //     nombre de la URG.
            $normalizar = function ($s) {
                $s = mb_strtolower(trim($s));
                $s = preg_replace('/[áàäâ]/u', 'a', $s);
                $s = preg_replace('/[éèëê]/u', 'e', $s);
                $s = preg_replace('/[íìïî]/u', 'i', $s);
                $s = preg_replace('/[óòöô]/u', 'o', $s);
                $s = preg_replace('/[úùüû]/u', 'u', $s);
                return $s;
            };

            $roIdsPoa = [];

            if ((int)$urgSeleccionada->unidad_responsable_gasto_id >= $urgEstructuralMin) {
                $roIdsPoa = DB::table('responsables_operativos')
                    ->where('ejercicio_id', $ejercicio_db_id)
                    ->where('unidad_responsable_gasto_id', $urgSeleccionada->unidad_responsable_gasto_id)
                    ->pluck('responsable_operativo_id')
                    ->toArray();
            } else {
                $urgNombreClean = $normalizar($urgSeleccionada->nombre);

                // Áreas del catálogo que NO tienen nombre idéntico al de la URG estructural:
                // DPyRF (4), DRH (5) y DRMySG (6) están agrupadas en la URG estructural
                // "Secretaría Administrativa" (UR código 04). Mapeo explícito por número de RO
                // (código POA estable UR=04, RO=10/11/12 en ambos ejercicios).
                $directorias = [
                    3 => '09', // Secretaría Administrativa
                    4 => '10', // Dirección de Planeación y Recursos Financieros
                    5 => '11', // Dirección de Recursos Humanos
                    6 => '12', // Dirección de Recursos Materiales y Servicios Generales
                ];

                $catalogoId = (int)$urgSeleccionada->unidad_responsable_gasto_id;
                if (isset($directorias[$catalogoId])) {
                    $roIdsPoa = DB::table('responsables_operativos')
                        ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
                        ->where('unidades_responsables_gastos.numero', '04')
                        ->where('responsables_operativos.numero', $directorias[$catalogoId])
                        ->pluck('responsables_operativos.responsable_operativo_id')
                        ->toArray();
                } else {
                    $urgEstructural = DB::table('unidades_responsables_gastos')
                        ->where('ejercicio_id', $ejercicio_db_id)
                        ->where('unidad_responsable_gasto_id', '>=', $urgEstructuralMin)
                        ->get()
                        ->first(function ($u) use ($normalizar, $urgNombreClean) {
                            return $normalizar($u->nombre) === $urgNombreClean;
                        });

                    if ($urgEstructural) {
                        $roIdsPoa = DB::table('responsables_operativos')
                            ->where('unidad_responsable_gasto_id', $urgEstructural->unidad_responsable_gasto_id)
                            ->pluck('responsable_operativo_id')
                            ->toArray();
                    } else {
                        $roIdsPoa = DB::table('responsables_operativos')
                            // Removed where('ejercicio_id') because some 2027 projects point to 2025/2026 ROs!
                            ->get()
                            ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
                                $roNorm = $normalizar($ro->nombre);
                                if ($roNorm === $urgNombreClean) return true;
                                
                                // Reemplazos para que hagan match Direccion con Director(a), etc.
                                $urgCore = str_replace(['direccion de ', 'direccion general ', 'unidad de ', 'coordinacion de ', 'la '], '', $urgNombreClean);
                                $urgCore = str_replace('juridica', 'juridic', $urgCore);
                                $urgCore = str_replace('secretaria administrativa', 'administrativo', $urgCore);
                                
                                return strlen($urgCore) > 5 && str_contains($roNorm, $urgCore);
                            })
                            ->pluck('responsable_operativo_id')
                            ->toArray();
                    }
                }
            }

            $rgIds = array_unique($roIdsPoa);
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
            $query->whereIn('proyectos.responsable_operativo_id', $rgIds);
        }

        $proyectos = $query->get();

        // Construir mapa inverso urg_estructural -> URG ID del catálogo 1-25
        // basado en nombre exacto (normalizado) y no en semejanza.
        $mapa_ro_urg = [];
        $todasUrgs = DB::table('unidades_responsables_gastos')->get();
        $urgsEstructurales = $todasUrgs->where('unidad_responsable_gasto_id', '>=', $urgEstructuralMin);

        $normalizar2 = function ($s) {
            $s = mb_strtolower(trim($s));
            $s = preg_replace('/[áàäâ]/u', 'a', $s);
            $s = preg_replace('/[éèëê]/u', 'e', $s);
            $s = preg_replace('/[íìïî]/u', 'i', $s);
            $s = preg_replace('/[óòöô]/u', 'o', $s);
            $s = preg_replace('/[úùüû]/u', 'u', $s);
            return $s;
        };

        foreach ($urgsEstructurales as $estruct) {
            $matchingCatalogo = $todasUrgs
                ->where('unidad_responsable_gasto_id', '<', $urgEstructuralMin)
                ->first(function ($u) use ($normalizar2, $estruct) {
                    return $normalizar2($u->nombre) === $normalizar2($estruct->nombre);
                });
            if ($matchingCatalogo) {
                $mapa_ro_urg[$estruct->unidad_responsable_gasto_id] = $matchingCatalogo->unidad_responsable_gasto_id;
            }
        }

        foreach ($proyectos as $p) {
            // Los riesgos se almacenan con el area_id del catálogo 1-25.
            // Si el área seleccionada es una URG estructural, traducimos a ese catálogo.
            $direct_area_id = ($area_id && $area_id !== 'todas')
                ? ((int)$area_id >= $urgEstructuralMin ? ($mapa_ro_urg[$p->urg_id] ?? (int)$area_id) : (int)$area_id)
                : ($mapa_ro_urg[$p->urg_id] ?? null);
            $p->riesgos_area = $direct_area_id
                ? DB::table('riesgos')->where('ejercicio_id', $ejercicio_db_id)->where('area_id', $direct_area_id)->select('id', 'local_id', 'riesgo')->get()
                : collect();
            $p->metas = DB::table('metas')->where('proyecto_id', $p->id)->get();
            $p->indicadores = DB::table('indicadores')->where('proyecto_id', $p->id)->get();

            // Actividades sustantivas
            $actividades = DB::table('actividades_sustantivas')->where('proyecto_id', $p->id)->get();
            
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

        // Deduplicar proyectos por nombre usando normalizar2
        $proyectosUnicos = collect();
        $nombresVistos = [];
        foreach ($proyectos as $p) {
            $nombreKey = $normalizar2($p->nombre ?? '');
            if (empty($nombreKey)) {
                // If the name is completely empty, we can just group it by some fallback or let it pass?
                // Actually, if it's empty, they might all be "Alineación técnica POA...". Let's deduplicate them too.
                $nombreKey = 'empty_project_name';
            }
            if (!isset($nombresVistos[$nombreKey])) {
                $nombresVistos[$nombreKey] = true;
                $proyectosUnicos->push($p);
            }
        }

        \Illuminate\Support\Facades\Log::info("Count proyectos inside getFichas: " . $proyectosUnicos->count());
        return response()->json($proyectosUnicos->values());
    }
}
