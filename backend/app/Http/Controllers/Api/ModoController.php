<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModoController extends Controller
{
    public function getEtapasActivas(Request $request)
    {
        // Buscar cualquier etapa habilitada, dando prioridad al ejercicio más reciente
        $etapaActiva = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->join('ejercicios', 'operaciones_ejercicios.ejercicio_id', '=', 'ejercicios.ejercicio_id')
            ->where('operaciones_ejercicios.habilitado', 'si')
            ->orderBy('ejercicios.ejercicio', 'desc')
            ->select('operaciones_ejercicios.ejercicio_id', 'ejercicios.ejercicio')
            ->first();

        if (!$etapaActiva) {
            // Si no hay ninguna etapa activa, caemos al año actual
            return response()->json([
                'ejercicio' => date('Y'),
                'etapas' => []
            ]);
        }

        $modosActivos = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->where('ejercicio_id', $etapaActiva->ejercicio_id)
            ->where('habilitado', 'si')
            ->pluck('tipo');

        return response()->json([
            'ejercicio' => $etapaActiva->ejercicio,
            'etapas' => $modosActivos
        ]);
    }

    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');

        $query = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->join('ejercicios', 'operaciones_ejercicios.ejercicio_id', '=', 'ejercicios.ejercicio_id')
            ->select('operaciones_ejercicios.*', 'ejercicios.ejercicio');

        if ($ejercicio) {
            $query->where('ejercicios.ejercicio', $ejercicio);
        }

        $modos = $query->orderBy('ejercicios.ejercicio', 'desc')->get();

        return response()->json($modos);
    }

    public function show($id)
    {
        $modo = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->where('operacion_ejercicio_id', $id)
            ->first();

        if (!$modo) {
            return response()->json(['message' => 'Modo no encontrado.'], 404);
        }

        // Obtener el ejercicio para saber las URs activas
        $ejercicio = DB::connection('poa_prod')
            ->table('ejercicios')
            ->where('ejercicio_id', $modo->ejercicio_id)
            ->first();

        // Obtener todas las URG del ejercicio
        $urs = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos')
            ->where('ejercicio_id', $modo->ejercicio_id)
            ->orderBy('numero')
            ->get();

        // Obtener la configuración específica por UR para este modo
        $urModes = DB::connection('poa_prod')
            ->table('operacion_ejercicio_ur')
            ->where('operacion_ejercicio_id', $id)
            ->get()
            ->keyBy('unidad_responsable_gasto_id');

        // Combinar datos
        $unidades = $urs->map(function($ur) use ($urModes) {
            $config = $urModes->get($ur->unidad_responsable_gasto_id);
            return [
                'unidad_responsable_gasto_id' => $ur->unidad_responsable_gasto_id,
                'numero' => $ur->numero,
                'nombre' => $ur->nombre,
                'habilitado' => $config ? $config->habilitado : 'no',
                'fecha_prorroga' => $config ? $config->fecha_prorroga : null
            ];
        });

        return response()->json([
            'modo' => $modo,
            'ejercicio' => $ejercicio ? $ejercicio->ejercicio : null,
            'unidades' => $unidades
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ejercicio' => 'required|integer',
            'tipo' => 'required|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio'
        ]);

        $ejercicioData = DB::connection('poa_prod')
            ->table('ejercicios')
            ->where('ejercicio', $request->ejercicio)
            ->first();

        if (!$ejercicioData) {
            return response()->json(['message' => 'El ejercicio ' . $request->ejercicio . ' no está dado de alta en la base de datos del sistema.'], 404);
        }

        // Verificar si ya existe un modo de este tipo para el ejercicio
        $exists = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->where('ejercicio_id', $ejercicioData->ejercicio_id)
            ->where('tipo', $request->tipo)
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Ya existe este modo para el ejercicio seleccionado.'], 400);
        }

        // Verificar si ya hay OTRA etapa habilitada en el sistema
        $otraActiva = DB::connection('poa_prod')
            ->table('operaciones_ejercicios')
            ->where('habilitado', 'si')
            ->first();

        if ($otraActiva) {
            return response()->json([
                'message' => 'Actualmente ya existe una etapa habilitada en el sistema. Debes deshabilitar la etapa activa antes de habilitar una nueva.'
            ], 400);
        }

        DB::connection('poa_prod')->beginTransaction();
        try {
            $id = DB::connection('poa_prod')->table('operaciones_ejercicios')->insertGetId([
                'ejercicio_id' => $ejercicioData->ejercicio_id,
                'tipo' => $request->tipo,
                'habilitado' => 'si',
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin
            ]);

            $stats = [];
            if ($request->tipo === 'elaboracion_proyectos') {
                $stats = $this->clonePreviousYearData($ejercicioData->ejercicio_id, $ejercicioData->ejercicio);

                // Auto-habilitar todas las UR clonadas para esta etapa
                $unidades = DB::connection('poa_prod')->table('unidades_responsables_gastos')
                    ->where('ejercicio_id', $ejercicioData->ejercicio_id)
                    ->get();
                    
                $inserts = $unidades->map(function ($u) use ($id) {
                    return [
                        'operacion_ejercicio_id' => $id,
                        'unidad_responsable_gasto_id' => $u->unidad_responsable_gasto_id,
                        'fecha_prorroga' => null
                    ];
                })->toArray();
                
                if (!empty($inserts)) {
                    DB::connection('poa_prod')->table('operacion_ejercicio_ur')->insert($inserts);
                }
            }

            DB::connection('poa_prod')->commit();
            return response()->json([
                'message' => 'Modo creado exitosamente.', 
                'id' => $id,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            DB::connection('poa_prod')->rollBack();
            \Illuminate\Support\Facades\Log::error("Error cloning previous year data: " . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error crítico durante la clonación. Se abortó toda la operación para evitar datos incompletos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'habilitado' => 'required|in:si,no',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'unidades' => 'nullable|array',
            'unidades.*.id' => 'required|integer',
            'unidades.*.fecha_prorroga' => 'nullable|date'
        ]);

        DB::beginTransaction();
        try {
            $modo = DB::connection('poa_prod')
                ->table('operaciones_ejercicios')
                ->where('operacion_ejercicio_id', $id)
                ->first();

            if (!$modo) {
                return response()->json(['message' => 'Modo no encontrado.'], 404);
            }

            // Si se quiere habilitar, verificar que no haya OTRA habilitada
            if ($request->habilitado === 'si') {
                $otraActiva = DB::connection('poa_prod')
                    ->table('operaciones_ejercicios')
                    ->where('habilitado', 'si')
                    ->where('operacion_ejercicio_id', '!=', $id)
                    ->first();
                    
                if ($otraActiva) {
                    return response()->json([
                        'message' => 'Actualmente ya existe una etapa habilitada en el sistema. Debes deshabilitar la etapa activa antes de habilitar una nueva.'
                    ], 400);
                }
            }

            // Actualizar datos globales del modo
            DB::connection('poa_prod')
                ->table('operaciones_ejercicios')
                ->where('operacion_ejercicio_id', $id)
                ->update([
                    'habilitado' => $request->habilitado,
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin' => $request->fecha_fin
                ]);

            // Actualizar tabla pivote
            // Primero, borrar configuraciones existentes para este modo
            DB::connection('poa_prod')->table('operacion_ejercicio_ur')
                ->where('operacion_ejercicio_id', $id)
                ->delete();

            if ($request->habilitado === 'si' && !empty($request->unidades)) {
                $inserts = array_map(function ($u) use ($id) {
                    return [
                        'operacion_ejercicio_id' => $id,
                        'unidad_responsable_gasto_id' => $u['id'],
                        'fecha_prorroga' => $u['fecha_prorroga'] ?? null
                    ];
                }, $request->unidades);

                DB::connection('poa_prod')->table('operacion_ejercicio_ur')->insert($inserts);
            }

            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Configuración actualizada']);
        } catch (\Exception $e) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'Error al actualizar', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $modo = DB::connection('poa_prod')->table('operaciones_ejercicios')->where('operacion_ejercicio_id', $id)->first();
        if (!$modo) {
            return response()->json(['message' => 'Etapa no encontrada'], 404);
        }

        DB::connection('poa_prod')->beginTransaction();
        try {
            $stats = [];
            if ($modo->tipo === 'elaboracion_proyectos') {
                $ejId = $modo->ejercicio_id;
                
                // Matar meses_metas_programadas
                $stats['meses_metas_programadas'] = DB::connection('poa_prod')->table('meses_metas_programadas')
                    ->whereIn('meta_id', function ($query) use ($ejId) {
                        $query->select('meta_id')->from('metas')
                              ->whereIn('proyecto_id', function ($q) use ($ejId) {
                                  $q->select('proyecto_id')->from('proyectos')
                                    ->whereIn('responsable_operativo_id', function ($q2) use ($ejId) {
                                        $q2->select('responsable_operativo_id')->from('responsables_operativos')
                                           ->whereIn('unidad_responsable_gasto_id', function ($q3) use ($ejId) {
                                               $q3->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                                  ->where('ejercicio_id', $ejId);
                                           });
                                    });
                              });
                    })->delete();

                // Matar indicadores
                $stats['indicadores'] = DB::connection('poa_prod')->table('indicadores')
                    ->whereIn('proyecto_id', function ($query) use ($ejId) {
                        $query->select('proyecto_id')->from('proyectos')
                              ->whereIn('responsable_operativo_id', function ($q) use ($ejId) {
                                  $q->select('responsable_operativo_id')->from('responsables_operativos')
                                    ->whereIn('unidad_responsable_gasto_id', function ($q2) use ($ejId) {
                                        $q2->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                           ->where('ejercicio_id', $ejId);
                                    });
                              });
                    })->delete();

                // Matar actividades sustantivas
                $stats['actividades_sustantivas'] = DB::connection('poa_prod')->table('actividades_sustantivas')
                    ->whereIn('proyecto_id', function ($query) use ($ejId) {
                        $query->select('proyecto_id')->from('proyectos')
                              ->whereIn('responsable_operativo_id', function ($q) use ($ejId) {
                                  $q->select('responsable_operativo_id')->from('responsables_operativos')
                                    ->whereIn('unidad_responsable_gasto_id', function ($q2) use ($ejId) {
                                        $q2->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                           ->where('ejercicio_id', $ejId);
                                    });
                              });
                    })->delete();

                // Matar alineaciones PEI
                $stats['pei_proyecto_alineaciones'] = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
                    ->whereIn('proyecto_id', function ($query) use ($ejId) {
                        $query->select('proyecto_id')->from('proyectos')
                              ->whereIn('responsable_operativo_id', function ($q) use ($ejId) {
                                  $q->select('responsable_operativo_id')->from('responsables_operativos')
                                    ->whereIn('unidad_responsable_gasto_id', function ($q2) use ($ejId) {
                                        $q2->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                           ->where('ejercicio_id', $ejId);
                                    });
                              });
                    })->delete();

                // Matar metas
                $stats['metas'] = DB::connection('poa_prod')->table('metas')
                    ->whereIn('proyecto_id', function ($query) use ($ejId) {
                        $query->select('proyecto_id')->from('proyectos')
                              ->whereIn('responsable_operativo_id', function ($q) use ($ejId) {
                                  $q->select('responsable_operativo_id')->from('responsables_operativos')
                                    ->whereIn('unidad_responsable_gasto_id', function ($q2) use ($ejId) {
                                        $q2->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                           ->where('ejercicio_id', $ejId);
                                    });
                              });
                    })->delete();



                // Matar proyectos
                $stats['proyectos'] = DB::connection('poa_prod')->table('proyectos')
                    ->whereIn('responsable_operativo_id', function ($q) use ($ejId) {
                        $q->select('responsable_operativo_id')->from('responsables_operativos')
                          ->whereIn('unidad_responsable_gasto_id', function ($q2) use ($ejId) {
                              $q2->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                                 ->where('ejercicio_id', $ejId);
                          });
                    })->delete();

                // Matar responsables_operativos
                $stats['responsables_operativos'] = DB::connection('poa_prod')->table('responsables_operativos')
                    ->whereIn('unidad_responsable_gasto_id', function ($q) use ($ejId) {
                        $q->select('unidad_responsable_gasto_id')->from('unidades_responsables_gastos')
                          ->where('ejercicio_id', $ejId);
                    })->delete();

                // Matar URGs
                $stats['unidades_responsables_gastos'] = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $ejId)->delete();

                // Matar Subprogramas
                $stats['subprogramas'] = DB::connection('poa_prod')->table('subprogramas')
                    ->whereIn('programa_id', function ($query) use ($ejId) {
                        $query->select('programa_id')->from('programas')->where('ejercicio_id', $ejId);
                    })->delete();

                // Matar Programas
                $stats['programas'] = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', $ejId)->delete();

                // Matar Unidades Medida
                $stats['unidades_medidas'] = DB::connection('poa_prod')->table('unidades_medidas')->where('ejercicio_id', $ejId)->delete();
            }

            DB::connection('poa_prod')->table('operacion_ejercicio_ur')
                ->where('operacion_ejercicio_id', $id)
                ->delete();
            
            DB::connection('poa_prod')->table('operaciones_ejercicios')
                ->where('operacion_ejercicio_id', $id)
                ->delete();
            
            DB::connection('poa_prod')->commit();
            return response()->json([
                'message' => 'Etapa eliminada correctamente',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'Error al eliminar', 'error' => $e->getMessage()], 500);
        }
    }

    private function clonePreviousYearData($nuevoEjercicioId, $nuevoEjercicioAnio)
    {
        $stats = [
            'unidades_medidas' => 0, 'programas' => 0, 'subprogramas' => 0,
            'unidades_responsables_gastos' => 0, 'responsables_operativos' => 0, 'proyectos' => 0, 'metas' => 0,
            'meses_metas_programadas' => 0, 'actividades_sustantivas' => 0, 'indicadores' => 0, 'pei_proyecto_alineaciones' => 0
        ];
        $oldEjercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', $nuevoEjercicioAnio - 1)->first();
        if (!$oldEjercicio) return $stats;
        $oldId = $oldEjercicio->ejercicio_id;

        // Check if data already exists in any of the top level catalogs (URG, Programas, Unidades Medida)
        $hasUrg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $nuevoEjercicioId)->exists();
        $hasPg = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', $nuevoEjercicioId)->exists();
        $hasUm = DB::connection('poa_prod')->table('unidades_medidas')->where('ejercicio_id', $nuevoEjercicioId)->exists();
        if ($hasUrg || $hasPg || $hasUm) return $stats; // Ya se migró o ya hay datos

        // Mapeos
        $mapUm = [];
        $mapPg = [];
        $mapSp = [];
        $mapUrg = [];
        $mapRo = [];
        $mapPy = [];
        $mapMeta = [];
        $mapPeiLineas = [];
        $mapPeiObjetivos = [];

        // 0. Clonar PEI
        $oldPei = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', $nuevoEjercicioAnio - 1)->first();
        $peiProgramaId = null;

        if ($oldPei) {
            $hasNewPei = DB::connection('poa_prod')->table('pei_programas')->where('ejercicio_anio', $nuevoEjercicioAnio)->first();
            if (!$hasNewPei) {
                $peiProgramaId = DB::connection('poa_prod')->table('pei_programas')->insertGetId([
                    'ejercicio_anio' => $nuevoEjercicioAnio,
                    'nombre' => $oldPei->nombre
                ]);

                $oldLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $oldPei->pei_programa_id)->get();
                foreach ($oldLineas as $linea) {
                    $newLineaId = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->insertGetId([
                        'pei_programa_id' => $peiProgramaId,
                        'numero' => $linea->numero,
                        'nombre' => $linea->nombre
                    ]);
                    $mapPeiLineas[$linea->pei_linea_estrategica_id] = $newLineaId;
                }

                if (!empty($mapPeiLineas)) {
                    $oldObjetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_linea_estrategica_id', array_keys($mapPeiLineas))->get();
                    foreach ($oldObjetivos as $obj) {
                        $newObjId = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->insertGetId([
                            'pei_linea_estrategica_id' => $mapPeiLineas[$obj->pei_linea_estrategica_id],
                            'numero' => $obj->numero,
                            'nombre' => $obj->nombre
                        ]);
                        $mapPeiObjetivos[$obj->pei_objetivo_estrategico_id] = $newObjId;
                    }
                }
            } else {
                $peiProgramaId = $hasNewPei->pei_programa_id;
                
                // Map existing PEI lineas and objetivos to avoid broken references
                $oldLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $oldPei->pei_programa_id)->get();
                $newLineas = DB::connection('poa_prod')->table('pei_lineas_estrategicas')->where('pei_programa_id', $peiProgramaId)->get();
                
                foreach ($oldLineas as $oldL) {
                    $newL = $newLineas->firstWhere('numero', $oldL->numero);
                    if ($newL) $mapPeiLineas[$oldL->pei_linea_estrategica_id] = $newL->pei_linea_estrategica_id;
                }

                $oldObjetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_linea_estrategica_id', $oldLineas->pluck('pei_linea_estrategica_id'))->get();
                $newObjetivos = DB::connection('poa_prod')->table('pei_objetivos_estrategicos')->whereIn('pei_linea_estrategica_id', $newLineas->pluck('pei_linea_estrategica_id'))->get();
                
                foreach ($oldObjetivos as $oldO) {
                    $nO = $newObjetivos->where('pei_linea_estrategica_id', $mapPeiLineas[$oldO->pei_linea_estrategica_id] ?? null)->where('numero', $oldO->numero)->first();
                    if ($nO) $mapPeiObjetivos[$oldO->pei_objetivo_estrategico_id] = $nO->pei_objetivo_estrategico_id;
                }
            }
        }

        // 0. Unidades Medida
        $unidades_medidas = DB::connection('poa_prod')->table('unidades_medidas')->where('ejercicio_id', $oldId)->get();
        $stats['unidades_medidas'] = count($unidades_medidas);
        foreach ($unidades_medidas as $um) {
            $newId = DB::connection('poa_prod')->table('unidades_medidas')->insertGetId([
                'ejercicio_id' => $nuevoEjercicioId,
                'numero' => $um->numero,
                'nombre' => $um->nombre,
                'descripcion' => $um->descripcion,
                'porcentajes' => $um->porcentajes,
            ]);
            $mapUm[$um->unidad_medida_id] = $newId;
        }

        // 1. Programas
        $programas = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', $oldId)->get();
        $stats['programas'] = count($programas);
        foreach ($programas as $p) {
            $newId = DB::connection('poa_prod')->table('programas')->insertGetId([
                'ejercicio_id' => $nuevoEjercicioId,
                'numero' => $p->numero,
                'nombre' => $p->nombre,
            ]);
            $mapPg[$p->programa_id] = $newId;
        }

        if (empty($mapPg)) return $stats;

        // 2. Subprogramas
        $subprogramas = DB::connection('poa_prod')->table('subprogramas')->whereIn('programa_id', array_keys($mapPg))->get();
        $stats['subprogramas'] = count($subprogramas);
        foreach ($subprogramas as $sp) {
            $newId = DB::connection('poa_prod')->table('subprogramas')->insertGetId([
                'programa_id' => $mapPg[$sp->programa_id],
                'numero' => $sp->numero,
                'nombre' => $sp->nombre,
            ]);
            $mapSp[$sp->subprograma_id] = $newId;
        }

        // 3. URG
        $urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $oldId)->get();
        $stats['unidades_responsables_gastos'] = count($urgs);
        foreach ($urgs as $urg) {
            $newId = DB::connection('poa_prod')->table('unidades_responsables_gastos')->insertGetId([
                'ejercicio_id' => $nuevoEjercicioId,
                'numero' => $urg->numero,
                'nombre' => $urg->nombre,
                'repite_proyecto' => $urg->repite_proyecto,
                'cerrada' => $urg->cerrada,
            ]);
            $mapUrg[$urg->unidad_responsable_gasto_id] = $newId;
        }

        if (empty($mapUrg)) return $stats;

        // 4. RO
        $ros = DB::connection('poa_prod')->table('responsables_operativos')->whereIn('unidad_responsable_gasto_id', array_keys($mapUrg))->get();
        $stats['responsables_operativos'] = count($ros);
        foreach ($ros as $ro) {
            $newId = DB::connection('poa_prod')->table('responsables_operativos')->insertGetId([
                'unidad_responsable_gasto_id' => $mapUrg[$ro->unidad_responsable_gasto_id],
                'numero' => $ro->numero,
                'nombre' => $ro->nombre,
            ]);
            $mapRo[$ro->responsable_operativo_id] = $newId;
            
            // Clone user assignments for this RO
            $userAssignments = DB::connection('poa_prod')->table('usuarios_responsables_operativos')
                ->where('responsable_operativo_id', $ro->responsable_operativo_id)
                ->get();
            
            $newAssignments = [];
            foreach ($userAssignments as $assignment) {
                $newAssignments[] = [
                    'usuario_poa_id' => $assignment->usuario_poa_id,
                    'responsable_operativo_id' => $newId,
                ];
            }
            if (!empty($newAssignments)) {
                DB::connection('poa_prod')->table('usuarios_responsables_operativos')->insert($newAssignments);
            }
        }

        if (empty($mapRo) || empty($mapSp)) return $stats;

        // 5. Proyectos
        $proyectos = DB::connection('poa_prod')->table('proyectos')->whereIn('responsable_operativo_id', array_keys($mapRo))->get();
        $clonedProjects = 0;
        $clonedAlineaciones = 0;
        foreach ($proyectos as $py) {
            if (!isset($mapSp[$py->subprograma_id])) continue;

            // Skip cancelled projects
            if (trim(strtoupper($py->nombre)) === 'BAJA') {
                continue;
            }

            $newId = DB::connection('poa_prod')->table('proyectos')->insertGetId([
                'responsable_operativo_id' => $mapRo[$py->responsable_operativo_id],
                'subprograma_id' => $mapSp[$py->subprograma_id],
                'numero' => $py->numero,
                'nombre' => $py->nombre,
                'tipo' => $py->tipo,
                'version' => 1,
                'objetivo' => $py->objetivo,
                'justificacion' => $py->justificacion,
                'descripcion' => $py->descripcion,
                'fecha' => now(),
                'nombre_responsable_operativo' => $py->nombre_responsable_operativo,
                'cargo_responsable_operativo' => $py->cargo_responsable_operativo,
                'nombre_titular' => $py->nombre_titular,
                'responsable_ficha' => $py->responsable_ficha,
                'autorizado_por' => $py->autorizado_por,
                'status' => 'Captura',
            ]);
            $mapPy[$py->proyecto_id] = $newId;
            $clonedProjects++;

            // Map PEI Alignments
            if ($peiProgramaId) {
                $oldAlineaciones = DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
                    ->where('proyecto_id', $py->proyecto_id)
                    ->get();
                
                foreach ($oldAlineaciones as $oa) {
                    $newLineaId = $mapPeiLineas[$oa->pei_linea_estrategica_id] ?? $oa->pei_linea_estrategica_id;
                    $newObjId = $mapPeiObjetivos[$oa->pei_objetivo_estrategico_id] ?? $oa->pei_objetivo_estrategico_id;

                    DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->insert([
                        'proyecto_id' => $newId,
                        'pei_programa_id' => $peiProgramaId,
                        'pei_linea_estrategica_id' => $newLineaId,
                        'pei_objetivo_estrategico_id' => $newObjId,
                    ]);
                    $clonedAlineaciones++;
                }
            }
        }

        $stats['proyectos'] = $clonedProjects;
        $stats['pei_proyecto_alineaciones'] = $clonedAlineaciones;

        if (empty($mapPy)) return $stats;

        // Acciones Sustantivas
        $actividades = DB::connection('poa_prod')->table('actividades_sustantivas')->whereIn('proyecto_id', array_keys($mapPy))->get();
        $targetTable = 'actividades_sustantivas';
        
        $insertsAct = [];
        foreach ($actividades as $act) {
            $insertsAct[] = [
                'proyecto_id' => $mapPy[$act->proyecto_id],
                'numero' => $act->numero ?? null,
                'descripcion' => $act->descripcion,
                'recursos_asociados' => $act->recursos_asociados ?? null,
            ];
        }
        if (!empty($insertsAct)) {
            $chunks = array_chunk($insertsAct, 500);
            foreach ($chunks as $chunk) {
                DB::connection('poa_prod')->table($targetTable)->insert($chunk);
            }
        }
        $stats['actividades_sustantivas'] = count($insertsAct);

        // Metas
        // Fetch all metas for these projects
        $metas = DB::connection('poa_prod')
            ->table('metas')
            ->whereIn('proyecto_id', array_keys($mapPy))
            ->orderByRaw('meta_padre_id IS NOT NULL, meta_padre_id ASC')
            ->get();
        
        $insertsMeses = [];
        foreach ($metas as $m) {
            $newPadreId = $m->meta_padre_id ? ($mapMeta[$m->meta_padre_id] ?? null) : null;
            $newUmId = isset($mapUm[$m->unidad_medida_id]) ? $mapUm[$m->unidad_medida_id] : $m->unidad_medida_id;
            
            $newMetaId = DB::connection('poa_prod')->table('metas')->insertGetId([
                'meta_padre_id' => $newPadreId,
                'proyecto_id' => $mapPy[$m->proyecto_id],
                'unidad_medida_id' => $newUmId,
                'tipo' => $m->tipo,
                'orden' => $m->orden,
                'nombre' => $m->nombre,
                'peso' => $m->peso,
                'tmc' => 2,
            ]);
            $mapMeta[$m->meta_id] = $newMetaId;
            $stats['metas']++;

            // Fetch meses_metas_programadas for this old meta
            $meses = DB::connection('poa_prod')->table('meses_metas_programadas')->where('meta_id', $m->meta_id)->get();
            foreach ($meses as $mes) {
                $insertsMeses[] = [
                    'meta_id' => $newMetaId,
                    'mes_id' => $mes->mes_id,
                    'numero' => 0,
                ];
            }
        }
        
        if (!empty($insertsMeses)) {
            $stats['meses_metas_programadas'] = count($insertsMeses);
            foreach (array_chunk($insertsMeses, 100) as $chunk) {
                DB::connection('poa_prod')->table('meses_metas_programadas')->insert($chunk);
            }
        }

        // Indicadores
        if (!empty($mapMeta)) {
            $indicadores = DB::connection('poa_prod')->table('indicadores')->whereIn('proyecto_id', array_keys($mapPy))->get();
            $oldMetas = DB::connection('poa_prod')->table('metas')->whereIn('proyecto_id', array_keys($mapPy))->get()->keyBy('meta_id');
            $newMetas = DB::connection('poa_prod')->table('metas')->whereIn('proyecto_id', array_values($mapPy))->get()->groupBy('proyecto_id');
            
            $insertsInds = [];
            
            // First pass: assign normal indicators to their respective new metas
            // and keep track of which new metas have an indicator.
            $newMetaHasIndicator = [];
            $principalIndicators = [];
            
            foreach ($indicadores as $ind) {
                $oldMeta = $oldMetas->get($ind->meta_id);
                if ($oldMeta && $oldMeta->tipo === 'principal') {
                    $principalIndicators[] = $ind;
                } else {
                    $newMetaId = isset($mapMeta[$ind->meta_id]) ? $mapMeta[$ind->meta_id] : $ind->meta_id;
                    $newMetaHasIndicator[$newMetaId] = true;
                    $insertsInds[] = [
                        'meta_id' => $newMetaId,
                        'proyecto_id' => $mapPy[$ind->proyecto_id],
                        'unidad_medida_id' => isset($mapUm[$ind->unidad_medida_id]) ? $mapUm[$ind->unidad_medida_id] : $ind->unidad_medida_id,
                        'dimension_id' => $ind->dimension_id,
                        'frecuencia_id' => $ind->frecuencia_id,
                        'nombre' => $ind->nombre,
                        'definicion' => $ind->definicion,
                        'metodo_calculo' => $ind->metodo_calculo,
                        'meta' => $ind->meta,
                        'id_metap' => $ind->id_metap ? ($mapMeta[$ind->id_metap] ?? $ind->id_metap) : null,
                        'id_metac' => $ind->id_metac ? ($mapMeta[$ind->id_metac] ?? $ind->id_metac) : null,
                    ];
                }
            }

            // Second pass: handle principal indicators
            foreach ($principalIndicators as $ind) {
                $newProyectoId = $mapPy[$ind->proyecto_id];
                $projectMetas = $newMetas->get($newProyectoId, collect());
                
                // Find an orphan complementaria meta
                $orphanMeta = $projectMetas->first(function($m) use (&$newMetaHasIndicator) {
                    return $m->tipo === 'complementaria' && !isset($newMetaHasIndicator[$m->meta_id]);
                });

                if ($orphanMeta) {
                    // Assign to the orphan complementaria
                    $newMetaHasIndicator[$orphanMeta->meta_id] = true;
                    $insertsInds[] = [
                        'meta_id' => $orphanMeta->meta_id,
                        'proyecto_id' => $newProyectoId,
                        'unidad_medida_id' => isset($mapUm[$ind->unidad_medida_id]) ? $mapUm[$ind->unidad_medida_id] : $ind->unidad_medida_id,
                        'dimension_id' => $ind->dimension_id,
                        'frecuencia_id' => $ind->frecuencia_id,
                        'nombre' => $ind->nombre,
                        'definicion' => $ind->definicion,
                        'metodo_calculo' => $ind->metodo_calculo,
                        'meta' => $ind->meta,
                        'id_metap' => $ind->id_metap ? ($mapMeta[$ind->id_metap] ?? $ind->id_metap) : null,
                        'id_metac' => $ind->id_metac ? ($mapMeta[$ind->id_metac] ?? $ind->id_metac) : null,
                    ];
                } else {
                    // No orphan found, keep it attached to the principal meta
                    $insertsInds[] = [
                        'meta_id' => isset($mapMeta[$ind->meta_id]) ? $mapMeta[$ind->meta_id] : $ind->meta_id,
                        'proyecto_id' => $newProyectoId,
                        'unidad_medida_id' => isset($mapUm[$ind->unidad_medida_id]) ? $mapUm[$ind->unidad_medida_id] : $ind->unidad_medida_id,
                        'dimension_id' => $ind->dimension_id,
                        'frecuencia_id' => $ind->frecuencia_id,
                        'nombre' => $ind->nombre,
                        'definicion' => $ind->definicion,
                        'metodo_calculo' => $ind->metodo_calculo,
                        'meta' => $ind->meta,
                        'id_metap' => $ind->id_metap ? ($mapMeta[$ind->id_metap] ?? $ind->id_metap) : null,
                        'id_metac' => $ind->id_metac ? ($mapMeta[$ind->id_metac] ?? $ind->id_metac) : null,
                    ];
                }
            }
            if (!empty($insertsInds)) {
                $stats['indicadores'] = count($insertsInds);
                foreach (array_chunk($insertsInds, 100) as $chunk) {
                    DB::connection('poa_prod')->table('indicadores')->insert($chunk);
                }
            }
        }
        return $stats;
    }
}
