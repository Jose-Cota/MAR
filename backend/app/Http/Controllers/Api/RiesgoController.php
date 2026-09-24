<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Riesgo;
use App\Models\ActividadSustantiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiesgoController extends Controller
{
    public function index(Request $request)
    {
        $query = Riesgo::with(['controles', 'indicadores', 'seguimientos_mensuales', 'evaluaciones_trimestrales']);

        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->has('ejercicio_id')) {
            // Check if the passed value is a year (e.g. 2027) instead of an ID
            $val = $request->ejercicio_id;
            if ($val > 2000) {
                $ej = DB::table('ejercicios')->where('ejercicio', $val)->first();
                if ($ej) {
                    $query->where('ejercicio_id', $ej->ejercicio_id);
                } else {
                    $query->where('ejercicio_id', $val);
                }
            } else {
                $query->where('ejercicio_id', $val);
            }
        }

        $riesgos = $query->get();
        $this->attachActividadesResueltas($riesgos);
        return response()->json($riesgos);
    }

    public function show($id)
    {
        $riesgo = Riesgo::with(['controles', 'indicadores', 'seguimientos_mensuales', 'evaluaciones_trimestrales'])->findOrFail($id);
        $this->attachActividadesResueltas(collect([$riesgo]));
        return response()->json($riesgo);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'objetivo' => 'nullable|string',
            'efectos_consecuencias' => 'nullable|string',
            'riesgo' => 'required|string',
            'factores' => 'nullable|string',
            'factores_internos' => 'nullable|string',
            'factores_externos' => 'nullable|string',
            'probabilidad' => 'nullable|integer',
            'impacto' => 'nullable|integer',
            'probabilidad_inicial' => 'nullable|integer',
            'impacto_inicial' => 'nullable|integer',
            'status' => 'nullable|string',
            'actividades' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            \Log::info("Starting store...");

            $areaIdStore = $request->area_id;

            // Generar local_id consecutivo por proyecto (area_id)
            $ejercicioId = $this->internalEjercicioId($request->ejercicio_id);
            $conteoExistente = Riesgo::where('area_id', $areaIdStore)
                ->where('ejercicio_id', $ejercicioId)
                ->count();
            $localId = 'R' . ($conteoExistente + 1);

            $datos = $request->all();
            $datos['area_id'] = $areaIdStore;
            $datos['local_id'] = $localId;
            $datos['ejercicio_id'] = $ejercicioId;

            $riesgo = Riesgo::create($datos);
            \Log::info("Riesgo created. ID: " . $riesgo->id . " local_id: " . $localId);

            if ($request->has('controles') && is_array($request->controles)) {
                foreach ($request->controles as $controlData) {
                    $riesgo->controles()->create($controlData);
                }
                \Log::info("Controles created.");
            }

            if ($request->has('indicadores') && is_array($request->indicadores)) {
                foreach ($request->indicadores as $indicadorData) {
                    $riesgo->indicadores()->create($indicadorData);
                }
            }

            $this->syncActividades($riesgo, $request->actividades);
            \Log::info("Actividades synced.");

            DB::commit();
            \Log::info("Committed.");
            
            $riesgoLoaded = $riesgo->load(['controles', 'indicadores']);
            $this->attachActividadesResueltas(collect([$riesgoLoaded]));
            $this->syncPonencias($riesgoLoaded, $request);
            
            return response()->json($riesgoLoaded, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in store: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $riesgo = Riesgo::findOrFail($id);

        DB::beginTransaction();
        try {
            $datos = $request->only([
                'local_id', 'area_id', 'ejercicio_id', 'objetivo', 'efectos_consecuencias',
                'riesgo', 'factores', 'factores_internos', 'factores_externos',
                'probabilidad', 'impacto', 'probabilidad_inicial', 'impacto_inicial',
                'status', 'last_observation',
            ]);

            // No translation needed

            // Nunca guardar el año como ejercicio_id: resolver al id interno (17/19)
            if (array_key_exists('ejercicio_id', $datos)) {
                $datos['ejercicio_id'] = $this->internalEjercicioId($datos['ejercicio_id']);
            }

            $riesgo->update($datos);

            if ($request->has('controles') && is_array($request->controles)) {
                $riesgo->controles()->delete();
                foreach ($request->controles as $controlData) {
                    $riesgo->controles()->create($controlData);
                }
            }

            if ($request->has('indicadores') && is_array($request->indicadores)) {
                $riesgo->indicadores()->delete();
                foreach ($request->indicadores as $indicadorData) {
                    $riesgo->indicadores()->create($indicadorData);
                }
            }

            if ($request->has('actividades')) {
                $this->syncActividades($riesgo, $request->actividades);
            }

            DB::commit();
            
            $riesgoLoaded = $riesgo->load(['controles', 'indicadores']);
            $this->attachActividadesResueltas(collect([$riesgoLoaded]));
            $this->syncPonencias($riesgoLoaded, $request);
            
            return response()->json($riesgoLoaded);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $riesgo = Riesgo::findOrFail($id);
        $riesgo->delete();
        return response()->json(null, 204);
    }

    public function validarControl(Request $request, $riesgoId, $controlId)
    {
        $request->validate([
            'estado_validacion' => 'required|string|in:Propuesto – pendiente de validación,Validado por el área'
        ]);

        $riesgo = Riesgo::findOrFail($riesgoId);
        $control = $riesgo->controles()->findOrFail($controlId);

        if ($request->estado_validacion === 'Validado por el área' && empty($control->evidencia_tipo) && empty($control->evidencia_referencia)) {
            return response()->json(['message' => 'Para validar el control registra primero evidencia o referencia verificable.'], 400);
        }

        $control->estado_validacion = $request->estado_validacion;
        $control->save();

        return response()->json(['message' => 'Control actualizado correctamente', 'control' => $control]);
    }

    public function batchValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'risk_ids' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Resolver año → ID interno del ejercicio
            $ejercicioId = $request->ejercicio_id;
            if ($ejercicioId > 2000) {
                $ej = DB::table('ejercicios')->where('ejercicio', $ejercicioId)->first();
                if ($ej) $ejercicioId = $ej->ejercicio_id;
            }

            Riesgo::whereIn('id', $request->risk_ids)
                ->where('area_id', $request->area_id)
                ->where('ejercicio_id', $ejercicioId)
                ->update(['status' => 'Validado']);
            
            DB::commit();
            return response()->json(['message' => 'Riesgos validados correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function batchUnvalidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required',
            'ejercicio_id' => 'required|integer',
            'risk_ids' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Resolver año → ID interno del ejercicio
            $ejercicioId = $request->ejercicio_id;
            if ($ejercicioId > 2000) {
                $ej = DB::table('ejercicios')->where('ejercicio', $ejercicioId)->first();
                if ($ej) $ejercicioId = $ej->ejercicio_id;
            }

            Riesgo::whereIn('id', $request->risk_ids)
                ->where('area_id', $request->area_id)
                ->where('ejercicio_id', $ejercicioId)
                ->update(['status' => 'Captura']);
            
            DB::commit();
            return response()->json(['message' => 'Riesgos regresados a Captura correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resuelve cada actividad vinculada al riesgo desde la BD (tabla nueva
     * "actividades_sustantivas" o tabla histórica "acciones_sustantivas")
     * y agrega su contexto de proyecto. Normaliza el id para que coincida
     * con el listado que devuelve /actividades-sustantivas.
     *
     * @param \Illuminate\Support\Collection $riesgos
     * @return void
     */
    private function attachActividadesResueltas($riesgos)
    {
        if ($riesgos->isEmpty()) {
            return;
        }

        $riesgoIds = $riesgos->pluck('id')->toArray();
        $pivots = DB::table('actividad_riesgo')
            ->whereIn('riesgo_id', $riesgoIds)
            ->orderBy('riesgo_id')
            ->orderBy('actividad_sustantiva_id')
            ->get()
            ->groupBy('riesgo_id');

        if ($pivots->isEmpty()) {
            $areaFallback = [];
            foreach ($riesgos as $r) {
                $r->setRelation('actividades', collect());
                $r->setAttribute('proyectos', []);
                $this->adjuntarFallbackArea($r, $areaFallback);
            }
            return;
        }

        $actIds = $pivots->flatten(1)->pluck('actividad_sustantiva_id')->unique()->values()->toArray();

        $nuevas = DB::table('actividades_sustantivas')->whereIn('id', $actIds)->get()->keyBy('id');
        $viejas = collect();

        $proyectoIds = collect([])
            ->merge($nuevas->pluck('proyecto_id'))
            ->unique()->values()->toArray();

        $proyectos = DB::table('proyectos')->whereIn('proyecto_id', $proyectoIds)->get()->keyBy('proyecto_id');
        $detalleProyectos = $this->detalleProyectos($proyectoIds);

        $ejercicioIds = $proyectos->pluck('ejercicio_id')->unique()->values()->toArray();
        $ejercicios = DB::table('ejercicios')->whereIn('ejercicio_id', $ejercicioIds)->get()->keyBy('ejercicio_id');

        $anioPorRiesgo = [];
        foreach ($riesgos as $r) {
            $anioPorRiesgo[$r->id] = $this->anioEjercicio($r->ejercicio_id, $ejercicios);
        }

        $areaFallback = [];
        foreach ($riesgos as $r) {
            $resueltas = [];
            $proyectosRiesgo = [];
            foreach ($pivots->get($r->id, collect()) as $p) {
                $a = $this->resolverActividad((int)$p->actividad_sustantiva_id, $nuevas, $viejas, $proyectos, $ejercicios, $anioPorRiesgo[$r->id], $detalleProyectos);
                if ($a) {
                    $resueltas[] = $a;
                    if (!empty($a['proyecto'])) {
                        $proyectosRiesgo[$a['proyecto']['proyecto_id']] = $a['proyecto'];
                    }
                }
            }
            $r->setRelation('actividades', collect($resueltas));
            $r->setAttribute('proyectos', array_values($proyectosRiesgo));

            if (empty($resueltas)) {
                $this->adjuntarFallbackArea($r, $areaFallback);
            }
        }
    }

    /**
     * Cuando un riesgo no tiene actividades vinculadas, se adjuntan como
     * referencia los proyectos de su Unidad Responsable y las actividades
     * de esos proyectos (datos reales de la BD). Se cachea por área+ejercicio
     * y solo se usa para despliegue, no para preseleccionar en el modal.
     */
    private function adjuntarFallbackArea($r, &$cache)
    {
        $datos = $this->datosArea($r->area_id, $r->ejercicio_id ?? null, $cache);
        $r->setAttribute('actividades_area', $datos['actividades']);
        $r->setAttribute('proyectos_area', $datos['proyectos']);
    }

    /**
     * Proyectos y actividades sustantivas de un área (UR) para el ejercicio
     * indicado. Cachea por área+ejercicio. Devuelve además los ids normalizados
     * para poder vincularlos como fallback al guardar.
     *
     * @param string|int|null $areaId
     * @param string|int|null $ejercicioId
     * @param array $cache
     * @return array{proyectos: array, actividades: array, ids: array}
     */
    private function datosArea($areaId, $ejercicioId, &$cache)
    {
        $clave = (string)$areaId . '|' . (string)$ejercicioId;
        if (isset($cache[$clave])) {
            return $cache[$clave];
        }

        $anio = null;
        $proyEjercicioId = null;
        if ($ejercicioId !== null && $ejercicioId !== '' && is_numeric($ejercicioId)) {
            if ((int)$ejercicioId > 2000) {
                // Llega el año (ej. 2027): resolver al id interno del ejercicio
                $anio = (int)$ejercicioId;
                $e = DB::table('ejercicios')->where('ejercicio', (int)$ejercicioId)->first();
                $proyEjercicioId = $e ? (int)$e->ejercicio_id : null;
            } else {
                // Llega el id interno (ej. 19)
                $proyEjercicioId = (int)$ejercicioId;
                $e = DB::table('ejercicios')->where('ejercicio_id', (int)$ejercicioId)->first();
                $anio = $e ? (int)$e->ejercicio : (int)$ejercicioId;
            }
        }

        $proyectosArea = DB::table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->where('urg.unidad_responsable_gasto_id', $areaId);

        $proyectosSel = (clone $proyectosArea)->select('py.*')->get()->keyBy('proyecto_id');
        $proyectosUsadosDelAnio = false;
        if ($proyEjercicioId !== null) {
            $candidatos = (clone $proyectosArea)
                ->where('py.ejercicio_id', $proyEjercicioId)
                ->select('py.*')
                ->get()
                ->keyBy('proyecto_id');
            if (!$candidatos->isEmpty()) {
                $proyectosSel = $candidatos;
                $proyectosUsadosDelAnio = true;
            }
        }

        $resultado = ['proyectos' => [], 'actividades' => [], 'ids' => []];

        if (!$proyectosSel->isEmpty()) {
            $proyIds = $proyectosSel->keys()->values()->all();
            $detalle = $this->detalleProyectos($proyIds);

            // Actividades propias del área (tablas nueva y vieja)
            $nuevasArea = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyIds)->get()->keyBy('id');
            $viejasArea = collect();

            if ($proyectosUsadosDelAnio) {
                // Para el ejercicio del riesgo se usa la tabla correspondiente
                $fuente = $nuevasArea;
                $ids = $fuente->keys()->values()
                    ->map(fn($id) => (string)$id)
                    ->all();
            } else {
                // Sin proyectos del año del riesgo: cualquier actividad del área
                $ids = collect($nuevasArea->keys())
                    ->map(fn($id) => (string)$id)
                    ->unique()
                    ->values()
                    ->all();
            }

            $resultado['ids'] = $ids;

            $resueltas = [];
            $proyOut = [];
            foreach ($ids as $actId) {
                $a = $this->resolverActividad((int)$actId, $nuevasArea, $viejasArea, $proyectosSel, collect(), $anio, $detalle);
                if ($a) {
                    $resueltas[] = $a;
                    if (!empty($a['proyecto'])) {
                        $proyOut[$a['proyecto']['proyecto_id']] = $a['proyecto'];
                    }
                }
            }

            $resultado['actividades'] = $resueltas;
            $resultado['proyectos'] = array_values($proyOut);
        }

        return $cache[$clave] = $resultado;
    }

    /**
     * Sincroniza las actividades vinculadas al riesgo. Si llega sin
     * actividades o vacío, vincula por fallback las del área (año del riesgo).
     */
    private function syncActividades(Riesgo $riesgo, $actividadesInput = [])
    {
        \Log::info("Syncing actividades (manual to avoid cross-connection deadlock)...");

        if (!is_array($actividadesInput)) {
            $actividadesInput = isset($actividadesInput)
                ? [$actividadesInput]
                : [];
        }

        if (empty($actividadesInput)) {
            $cache = [];
            $actividadesInput = $this->datosArea($riesgo->area_id, $riesgo->ejercicio_id ?? null, $cache)['ids'];
            if (!empty($actividadesInput)) {
                \Log::info("Sin actividades; fallback por área: " . count($actividadesInput) . " actividades vinculadas.");
            }
        }

        \Illuminate\Support\Facades\DB::table('actividad_riesgo')->where('riesgo_id', $riesgo->id)->delete();
        $insertData = [];
        foreach ($actividadesInput as $act) {
            $act_id = $this->normalizarIdActividad($act);
            if ($act_id === null) continue;
            $insertData[] = [
                'riesgo_id' => $riesgo->id,
                'actividad_sustantiva_id' => $act_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($insertData)) {
            \Illuminate\Support\Facades\DB::table('actividad_riesgo')->insert($insertData);
        }
    }

    /**
     * Decide de cuál tabla proviene la actividad del pivot y construye el
     * registro normalizado (con su proyecto).
     */
    private function resolverActividad($id, $nuevas, $viejas, $proyectos, $ejercicios, $anioRiesgo, $detalleProyectos = null)
    {
        $nueva = $nuevas->get($id);
        $vieja = $viejas->get($id);

        $elegida = null;
        if ($nueva && $vieja) {
            $anioNueva = $nueva ? $this->anioProyecto($nueva->proyecto_id, $proyectos, $ejercicios) : null;
            $anioVieja = $vieja ? $this->anioProyecto($vieja->proyecto_id, $proyectos, $ejercicios) : null;

            $nuevaCoincide = $anioNueva !== null && (int)$anioNueva === (int)$anioRiesgo;
            $viejaCoincide = $anioVieja !== null && (int)$anioVieja === (int)$anioRiesgo;

            if ($nuevaCoincide === $viejaCoincide) {
                $elegida = (int)$anioRiesgo >= 2027 ? [$nueva, 'id'] : [$vieja, 'accion_sustantiva_id'];
            } else {
                $elegida = $nuevaCoincide ? [$nueva, 'id'] : [$vieja, 'accion_sustantiva_id'];
            }
        } elseif ($nueva) {
            $elegida = [$nueva, 'id'];
        } elseif ($vieja) {
            $elegida = [$vieja, 'accion_sustantiva_id'];
        } else {
            return null;
        }

        [$fila, $pk] = $elegida;
        $proyecto = $proyectos->get($fila->proyecto_id);

        $salida = [
            'id' => (string)$fila->{$pk},
            'accion_sustantiva_id' => (string)$fila->{$pk},
            'proyecto_id' => $fila->proyecto_id,
            'numero' => $fila->numero,
            'descripcion' => $fila->descripcion,
            'recursos_asociados' => $fila->recursos_asociados,
        ];

        if ($proyecto) {
            $detalle = $detalleProyectos ? $detalleProyectos->get($proyecto->proyecto_id) : null;
            $salida['proyecto'] = [
                'proyecto_id' => $proyecto->proyecto_id,
                'numero' => $proyecto->numero,
                'nombre' => $proyecto->nombre,
                'ejercicio_id' => $proyecto->ejercicio_id,
                'ejercicio' => $this->anioEjercicio($proyecto->ejercicio_id, $ejercicios),
                'clave' => $detalle ? $this->claveProyecto($detalle) : null,
                'urg_num' => $detalle->urg_num ?? null,
                'ro_num' => $detalle->ro_num ?? null,
                'pg_num' => $detalle->pg_num ?? null,
                'sp_num' => $detalle->sp_num ?? null,
                'py_num' => $detalle->py_num ?? null,
            ];
        }

        return $salida;
    }

    /**
     * Trae de la BD la clave programática (urg-ro-pg-sp-py) de cada proyecto.
     */
    private function detalleProyectos(array $proyectoIds)
    {
        if (empty($proyectoIds)) {
            return collect();
        }

        return DB::table('proyectos as py')
            ->leftJoin('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->leftJoin('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->leftJoin('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->leftJoin('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->whereIn('py.proyecto_id', $proyectoIds)
            ->select(
                'py.proyecto_id',
                'py.numero as py_num',
                'py.nombre',
                'urg.numero as urg_num',
                'ro.numero as ro_num',
                'pg.numero as pg_num',
                'sp.numero as sp_num'
            )
            ->get()
            ->keyBy('proyecto_id');
    }

    private function claveProyecto($detalle)
    {
        return implode('-', array_filter([
            $detalle->urg_num, $detalle->ro_num, $detalle->pg_num, $detalle->sp_num, $detalle->py_num,
        ], function ($v) {
            return $v !== null && $v !== '';
        }));
    }

    private function anioProyecto($proyectoId, $proyectos, $ejercicios)
    {
        $p = $proyectos->get($proyectoId);
        return $p ? $this->anioEjercicio($p->ejercicio_id, $ejercicios) : null;
    }

    private function anioEjercicio($ejercicioId, $ejercicios)
    {
        $e = $ejercicios->get($ejercicioId);
        return $e ? (int)$e->ejercicio : (is_numeric($ejercicioId) ? (int)$ejercicioId : null);
    }

    /**
     * Convierte un año (ej. 2026/2027) al id interno del ejercicio (17/19).
     * Si ya es un id interno, lo devuelve tal cual.
     */
    private function internalEjercicioId($val)
    {
        if (is_numeric($val) && (int)$val > 2000) {
            $e = DB::table('ejercicios')->where('ejercicio', (int)$val)->first();
            if ($e) {
                return (int)$e->ejercicio_id;
            }
        }
        return $val;
    }

    /**
     * Acepta un escalar o un objeto/array de actividad y devuelve su id.
     */
    private function normalizarIdActividad($act)
    {
        if (is_scalar($act)) {
            return (string)$act;
        }
        if (is_array($act)) {
            $id = $act['id'] ?? $act['accion_sustantiva_id'] ?? $act['actividad_sustantiva_id'] ?? null;
            return $id !== null ? (string)$id : null;
        }
        if (is_object($act)) {
            $id = $act->id ?? $act->accion_sustantiva_id ?? $act->actividad_sustantiva_id ?? null;
            return $id !== null ? (string)$id : null;
        }
        return null;
    }

    private function syncPonencias(Riesgo $riesgo, Request $request)
    {
        $ponencias = ['PAAH', 'PJHR', 'POVR', 'PKSL', 'PLPJC'];
        
        if (!in_array($riesgo->area_id, $ponencias)) {
            return;
        }

        // Extract the R part from local_id, e.g. PAAH-2026-R1 -> R1
        $parts = explode('-', $riesgo->local_id);
        $suffix = end($parts);
        if (empty($suffix) || $suffix[0] !== 'R') {
            return;
        }

        foreach ($ponencias as $ponenciaArea) {
            if ($ponenciaArea === $riesgo->area_id) continue;

            $expectedLocalId = $ponenciaArea . '-' . $riesgo->ejercicio_id . '-' . $suffix;
            $peer = Riesgo::where('local_id', $expectedLocalId)
                          ->where('ejercicio_id', $riesgo->ejercicio_id)
                          ->first();

            if ($peer) {
                // Copy fields
                $peer->update([
                    'objetivo' => $riesgo->objetivo,
                    'riesgo' => $riesgo->riesgo,
                    'probabilidad' => $riesgo->probabilidad,
                    'impacto' => $riesgo->impacto,
                    'factores_internos' => $riesgo->factores_internos,
                    'factores_externos' => $riesgo->factores_externos,
                ]);

                // Sync controls
                if ($request->has('controles') && is_array($request->controles)) {
                    $peer->controles()->delete();
                    foreach ($request->controles as $c) {
                        $newC = $c;
                        unset($newC['id']); // Let DB auto-increment
                        unset($newC['riesgo_id']);
                        $peer->controles()->create($newC);
                    }
                }

                // Sync indicators
                if ($request->has('indicadores') && is_array($request->indicadores)) {
                    $peer->indicadores()->delete();
                    foreach ($request->indicadores as $i) {
                        $newI = $i;
                        unset($newI['id']);
                        unset($newI['riesgo_id']);
                        $peer->indicadores()->create($newI);
                    }
                }
            }
        }
    }
}
