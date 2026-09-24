<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProyectoController extends Controller
{
    private function applyAccessScope($query, ?\App\Models\User $user)
    {
        $isAdministrador = $user && ($user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->hasRole('DPyRF') || $user->roles->pluck('name')->contains('Administrador') || $user->roles->pluck('name')->contains('DPyRF'));

        if ($user && !$isAdministrador) {
            $query->where(function($q) use ($user) {
                // Get the user's URG numbers based on their assigned URs
                $userUrgNumbers = $user->unidadesResponsables->pluck('numero')->toArray();
                if (empty($userUrgNumbers)) {
                    $userUrgNumbers = DB::connection('poa_prod')
                        ->table('unidades_responsables_gastos')
                        ->where('unidad_responsable_gasto_id', $user->area_id)
                        ->pluck('numero')->toArray();
                }

                $q->whereIn('urg.numero', $userUrgNumbers)
                  ->whereExists(function($subquery) use ($user) {
                      $subquery->select(DB::raw(1))
                               ->from('usuarios_responsables_operativos as uro')
                               ->whereColumn('ro.responsable_operativo_id', 'uro.responsable_operativo_id') 
                               ->where('uro.usuario_poa_id', $user->usuario_poa_id);
                  });
            });
        }

        return $query;
    }

    private function getPeiProgramByYear(int $ejercicioAnio)
    {
        return DB::table('pei_programas')
            ->where('ejercicio_anio', $ejercicioAnio)
            ->first();
    }

    private function getPeiLineasWithObjetivos(int $peiProgramaId, ?int $subprogramaId = null)
    {
        $lineasQuery = DB::table('pei_lineas_estrategicas')
            ->where('pei_programa_id', $peiProgramaId);

        $hasMappings = false;
        if ($subprogramaId) {
            $hasMappings = DB::table('subprograma_pei_alineaciones')->where('subprograma_id', $subprogramaId)->exists();
            if ($hasMappings) {
                // Find the mapped numeros from subprograma_pei_alineaciones
                $mappedNumeros = DB::table('subprograma_pei_alineaciones as spa')
                    ->join('pei_lineas_estrategicas as ple', 'spa.pei_linea_estrategica_id', '=', 'ple.pei_linea_estrategica_id')
                    ->where('spa.subprograma_id', $subprogramaId)
                    ->pluck('ple.numero');

                $lineasQuery->whereIn('numero', $mappedNumeros);
            }
        }

        $lineas = $lineasQuery->orderBy('numero')->get();

        if ($lineas->isEmpty()) {
            return [];
        }

        $lineaIds = $lineas->pluck('pei_linea_estrategica_id')->all();

        $objetivosQuery = DB::table('pei_objetivos_estrategicos')
            ->whereIn('pei_linea_estrategica_id', $lineaIds);

        if ($subprogramaId && $hasMappings) {
            // Find the mapped objetivo numeros
            $mappedObjNumeros = DB::table('subprograma_pei_alineaciones as spa')
                ->join('pei_objetivos_estrategicos as poe', 'spa.pei_objetivo_estrategico_id', '=', 'poe.pei_objetivo_estrategico_id')
                ->where('spa.subprograma_id', $subprogramaId)
                ->pluck('poe.numero');
                
            $objetivosQuery->whereIn('numero', $mappedObjNumeros);
        }

        $objetivos = $objetivosQuery->orderBy('numero')->get();

        $objetivosByLinea = [];
        foreach ($objetivos as $objetivo) {
            $objetivosByLinea[$objetivo->pei_linea_estrategica_id][] = [
                'id' => $objetivo->pei_objetivo_estrategico_id,
                'numero' => $objetivo->numero,
                'nombre' => $objetivo->nombre,
            ];
        }

        return $lineas->map(function ($linea) use ($objetivosByLinea) {
            return [
                'id' => $linea->pei_linea_estrategica_id,
                'numero' => $linea->numero,
                'nombre' => $linea->nombre,
                'objetivos' => $objetivosByLinea[$linea->pei_linea_estrategica_id] ?? [],
            ];
        })->values()->all();
    }

    private function getProyectoMetas(int $proyectoId)
    {
        $metas = DB::connection('poa_prod')
            ->table('metas as m')
            ->leftJoin('unidades_medidas as um', 'm.unidad_medida_id', '=', 'um.unidad_medida_id')
            ->select(
                'm.meta_id',
                'm.meta_padre_id',
                'm.proyecto_id',
                'm.tipo',
                'm.orden',
                'm.nombre',
                'm.unidad_medida_id',
                'um.nombre as unidad_medida',
                'm.peso',
                'm.tmc'
            )
            ->where('m.proyecto_id', $proyectoId)
            ->orderBy('m.tipo')
            ->orderBy('m.orden')
            ->orderBy('m.meta_id')
            ->get();

        if ($metas->isEmpty()) {
            return [];
        }

        $metaIds = $metas->pluck('meta_id')->all();
        $mesesList = DB::connection('poa_prod')
            ->table('meses_metas_programadas')
            ->select('meta_id', 'mes_id', DB::raw('SUM(numero) as total'))
            ->whereIn('meta_id', $metaIds)
            ->groupBy('meta_id', 'mes_id')
            ->get();

        $mesesByMeta = [];
        foreach ($mesesList as $mes) {
            $mesesByMeta[$mes->meta_id][(int) $mes->mes_id] = (float) $mes->total;
        }

        $indicadoresList = DB::connection('poa_prod')
            ->table('indicadores as i')
            ->leftJoin('frecuencias as f', 'i.frecuencia_id', '=', 'f.frecuencia_id')
            ->leftJoin('dimensiones as d', 'i.dimension_id', '=', 'd.dimension_id')
            ->whereIn('i.meta_id', $metaIds)
            ->select('i.*', 'f.nombre as frecuencia_nombre', 'd.nombre as dimension_nombre')
            ->get();

        $indicadoresByMeta = [];
        foreach ($indicadoresList as $ind) {
            $indicadoresByMeta[$ind->meta_id][] = $ind;
        }

        return $metas->map(function ($meta) use ($mesesByMeta, $indicadoresByMeta) {
            $meses = array_fill(1, 12, 0);
            $totalAnual = 0;

            foreach (($mesesByMeta[$meta->meta_id] ?? []) as $mesId => $valor) {
                $meses[$mesId] = $valor;
                $totalAnual += $valor;
            }

            return [
                'id' => $meta->meta_id,
                'meta_padre_id' => $meta->meta_padre_id,
                'proyecto_id' => $meta->proyecto_id,
                'tipo' => $meta->tipo,
                'orden' => $meta->orden,
                'nombre' => $meta->nombre,
                'unidad_medida_id' => $meta->unidad_medida_id,
                'unidad_medida' => $meta->unidad_medida,
                'peso' => $meta->peso,
                'tmc' => $meta->tmc,
                'meses' => $meses,
                'total_anual' => $totalAnual,
                'indicadores' => $indicadoresByMeta[$meta->meta_id] ?? [],
            ];
        })->values()->all();
    }

    private function getUnidadMedidasByEjercicio(int $ejercicioId)
    {
        return DB::connection('poa_prod')
            ->table('unidades_medidas')
            ->select('unidad_medida_id as id', 'numero', 'nombre', 'descripcion', 'porcentajes')
            ->where('ejercicio_id', $ejercicioId)
            ->orderBy('numero')
            ->get();
    }

    /**
     * Store a newly created proyecto in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subprograma_id' => 'required|integer',
            'responsable_operativo_id' => 'required|integer',
            'numero' => 'required|string|max:3',
            'nombre' => 'required|string|max:1024',
        ]);

        $query = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos as urg')
            ->join('responsables_operativos as ro', 'urg.unidad_responsable_gasto_id', '=', 'ro.unidad_responsable_gasto_id')
            ->select('urg.ejercicio_id')
            ->where('ro.responsable_operativo_id', $request->responsable_operativo_id);
        
        $urgInfo = $query->first();
        if (!$urgInfo) {
            return response()->json(['message' => 'Responsable operativo no encontrado'], 404);
        }

        // Insert new project with defaults for non-nullable fields
        $proyectoId = DB::connection('poa_prod')->table('proyectos')->insertGetId([
            'responsable_operativo_id' => $request->responsable_operativo_id,
            'subprograma_id' => $request->subprograma_id,
            'ejercicio_id' => $urgInfo->ejercicio_id,
            'numero' => $request->numero,
            'nombre' => $request->nombre,
            'tipo' => 'Nuevo',
            'version' => 1,
            'objetivo' => '',
            'justificacion' => '',
            'descripcion' => '',
            'fecha' => now()->toDateString(),
            'nombre_responsable_operativo' => '',
            'cargo_responsable_operativo' => '',
            'nombre_titular' => '',
            'responsable_ficha' => '',
            'autorizado_por' => '',
            'status' => 'Captura',
        ]);

        return response()->json(['message' => 'Proyecto creado exitosamente', 'proyecto_id' => $proyectoId], 201);
    }

    /**
     * Display a listing of the proyectos.
     */
    public function index(Request $request)
    {
        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select(
                'py.proyecto_id',
                'urg.numero as urg',
                'ro.numero as ro',
                'pg.numero as pg',
                'sp.numero as sp',
                'py.numero as py',
                'py.nombre as denominacion',
                'py.status as estatus'
            );

        $query = $this->applyAccessScope($query, $request->user());

        if ($request->has('ejercicio') && !empty($request->ejercicio)) {
            $query->join('ejercicios as ej', 'pg.ejercicio_id', '=', 'ej.ejercicio_id')
                  ->where('ej.ejercicio', $request->ejercicio);
        }

        $query->orderBy('urg.numero')
              ->orderBy('ro.numero')
              ->orderBy('pg.numero')
              ->orderBy('sp.numero')
              ->orderBy('py.numero');

        \Log::info('Proyectos Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        
        $proyectos = $query->get();

        return response()->json($proyectos);
    }

    public function fichaDescriptiva(Request $request, $id)
    {
        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select(
                'py.proyecto_id as id',
                'py.numero as py',
                'py.nombre as proyecto_nombre',
                'py.descripcion',
                'py.objetivo',
                'py.justificacion',
                'py.responsable_ficha',
                'py.puesto_responsable_ficha',
                'py.autorizante_nombre',
                'py.autorizante_puesto',
                'py.cargo_responsable_operativo',
                'py.nombre_titular',
                'py.autorizado_por',
                'urg.numero as urg',
                'urg.nombre as urg_nombre',
                'ro.numero as ro',
                'ro.nombre as responsable_operativo',
                'pg.numero as pg',
                'pg.nombre as programa_nombre',
                'sp.numero as sp',
                'sp.nombre as subprograma_nombre',
                'sp.subprograma_id',
                'urg.unidad_responsable_gasto_id as urg_id',
                'ej.ejercicio as ejercicio_anio',
                'ej.ejercicio_id as ejercicio_id',
                'py.status as estatus'
            )
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $peiPrograma = $this->getPeiProgramByYear((int) $proyecto->ejercicio_anio);
        $peiLineas = [];
        $peiAlineaciones = DB::table('pei_proyecto_alineaciones')
            ->where('proyecto_id', $id)
            ->get();

        if ($peiPrograma) {
            $peiLineas = $this->getPeiLineasWithObjetivos((int) $peiPrograma->pei_programa_id, $proyecto->subprograma_id);
        }

        $metas = $this->getProyectoMetas($id);
        $actividades = $this->getActividades($id);
        $unidadesMedida = $this->getUnidadMedidasByEjercicio((int) $proyecto->ejercicio_id);

        $responsablesFicha = DB::connection('poa_prod')
            ->table('usuarios_poa as u')
            ->join('usuario_unidad_responsable as pivot', 'u.usuario_poa_id', '=', 'pivot.usuario_poa_id')
            ->join('unidades_responsables_gastos as urg_u', 'pivot.unidad_responsable_gasto_id', '=', 'urg_u.unidad_responsable_gasto_id')
            ->leftJoin('puestos as p', 'u.usuario_poa_id', '=', 'p.usuario_poa_id')
            ->select(
                'u.usuario_poa_id as id',
                'u.usuario',
                'u.nombre',
                'u.apellido_paterno',
                'u.apellido_materno',
                'p.nombre as puesto',
                DB::raw("TRIM(CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno)) as label")
            )
            ->where('urg_u.numero', $proyecto->urg)
            ->orderBy('u.nombre')
            ->orderBy('u.apellido_paterno')
            ->orderBy('u.apellido_materno')
            ->get();

        $responsableActual = trim((string) ($proyecto->responsable_ficha ?? ''));
        if ($responsableActual !== '' && !$responsablesFicha->contains('label', $responsableActual)) {
            $responsablesFicha->prepend((object) [
                'id' => null,
                'usuario' => null,
                'nombre' => null,
                'apellido_paterno' => null,
                'apellido_materno' => null,
                'label' => $responsableActual,
            ]);
        }

        $dbCat = env('DB_CATALOGOS', 'bd_11Mayo2026');
        $puestosUr = DB::connection('poa_prod')->select("
            SELECT DISTINCT p.description as puesto
            FROM {$dbCat}.catAreas a
            JOIN {$dbCat}.catEmpleados e ON a.idArea = e.idArea
            JOIN {$dbCat}.catPlazas p ON e.idPlaza = p.id
            WHERE a.area = ? OR a.area LIKE CONCAT('%', ?, '%')
            ORDER BY p.description ASC
        ", [$proyecto->urg_nombre, $proyecto->urg_nombre]);

        $empleadosUr = DB::connection('poa_prod')->select("
            SELECT DISTINCT TRIM(CONCAT(e.nombre, ' ', e.apellidoPaterno, ' ', e.apellidoMaterno)) as label, p.description as puesto
            FROM {$dbCat}.catAreas a
            JOIN {$dbCat}.catEmpleados e ON a.idArea = e.idArea
            JOIN {$dbCat}.catPlazas p ON e.idPlaza = p.id
            WHERE a.area = ? OR a.area LIKE CONCAT('%', ?, '%')
            ORDER BY label ASC
        ", [$proyecto->urg_nombre, $proyecto->urg_nombre]);

        return response()->json([
            'proyecto' => $proyecto,
            'responsable_ficha_options' => $responsablesFicha,
            'puestos_ur' => array_column($puestosUr, 'puesto'),
            'empleados_ur' => $empleadosUr,
            'pei' => [
                'programa' => $peiPrograma ? [
                    'id' => $peiPrograma->pei_programa_id,
                    'ejercicio_anio' => $peiPrograma->ejercicio_anio,
                    'nombre' => $peiPrograma->nombre,
                ] : null,
                'lineas' => $peiLineas,
                'alineaciones' => $peiAlineaciones->map(function ($alineacion) {
                    return [
                        'linea_id' => $alineacion->pei_linea_estrategica_id,
                        'objetivo_id' => $alineacion->pei_objetivo_estrategico_id,
                        'programa_id' => $alineacion->pei_programa_id,
                    ];
                })->toArray(),
            ],
            'metas' => $metas,
            'actividades' => $actividades,
            'unidad_medidas' => $unidadesMedida,
        ]);
    }

    public function fichaDescriptivaPdf(Request $request, $id)
    {
        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select(
                'py.proyecto_id as id',
                'py.numero as py',
                'py.nombre as proyecto_nombre',
                'py.descripcion',
                'py.objetivo',
                'py.justificacion',
                'py.responsable_ficha',
                'py.puesto_responsable_ficha',
                'py.autorizante_nombre',
                'py.autorizante_puesto',
                'py.cargo_responsable_operativo',
                'py.nombre_titular',
                'py.autorizado_por',
                'urg.numero as urg',
                'urg.nombre as urg_nombre',
                'ro.numero as ro',
                'ro.nombre as responsable_operativo',
                'pg.numero as pg',
                'pg.nombre as programa_nombre',
                'sp.numero as sp',
                'sp.nombre as subprograma_nombre',
                'sp.subprograma_id',
                'urg.unidad_responsable_gasto_id as urg_id',
                'ej.ejercicio as ejercicio_anio',
                'ej.ejercicio_id as ejercicio_id',
                'py.nombre_responsable_operativo',
                'py.status as estatus',
                'py.fecha_cierre',
                'py.fecha_verificacion'
            )
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $peiPrograma = $this->getPeiProgramByYear((int) $proyecto->ejercicio_anio);
        $peiLineas = [];
        $peiAlineaciones = DB::table('pei_proyecto_alineaciones')
            ->where('proyecto_id', $id)
            ->get();

        if ($peiPrograma) {
            $peiLineas = $this->getPeiLineasWithObjetivos((int) $peiPrograma->pei_programa_id, $proyecto->subprograma_id);
        }

        $metas = $this->getProyectoMetas($id);
        $actividades = $this->getActividades($id);

        $logoPath = base_path('../frontend/src/assets/logo_tecdmx.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $data = [
            'proyecto' => $proyecto,
            'pei' => [
                'programa' => $peiPrograma ? [
                    'id' => $peiPrograma->pei_programa_id,
                    'ejercicio_anio' => $peiPrograma->ejercicio_anio,
                    'nombre' => $peiPrograma->nombre,
                ] : null,
                'lineas' => $peiLineas,
                'alineaciones' => $peiAlineaciones->map(function ($alineacion) {
                    return [
                        'linea_id' => $alineacion->pei_linea_estrategica_id,
                        'objetivo_id' => $alineacion->pei_objetivo_estrategico_id,
                    ];
                })->toArray(),
            ],
            'metas' => $metas,
            'actividades' => $actividades,
            'logo' => $logoBase64,
        ];

        $pdf = Pdf::loadView('exports.ficha_descriptiva_pdf', $data);
        return $pdf->stream('ficha_descriptiva_'.$proyecto->py.'.pdf');
    }

    private function getActividades(int $proyectoId)
    {
        $proyecto = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select('ej.ejercicio')
            ->where('py.proyecto_id', $proyectoId)
            ->first();

        $table = 'actividades_sustantivas';
        $pk = 'id';

        return DB::connection('poa_prod')
            ->table($table)
            ->where('proyecto_id', $proyectoId)
            ->select($pk.' as id', 'proyecto_id', 'descripcion', 'recursos_asociados', 'numero as orden')
            ->orderBy('numero')
            ->get();
    }

    public function updateResponsableFicha(Request $request, $id)
    {
        $request->validate([
            'responsable_ficha' => 'nullable|string|max:255',
            'puesto_responsable_ficha' => 'nullable|string|max:255',
            'justificacion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'objetivo' => 'nullable|string',
            'proyecto_nombre' => 'nullable|string|max:1024',
        ]);

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select(
                'py.proyecto_id',
                'urg.numero as urg_numero',
                'py.nombre',
                'py.responsable_ficha',
                'py.puesto_responsable_ficha',
                'py.autorizante_nombre',
                'py.autorizante_puesto',
                'py.justificacion',
                'py.descripcion',
                'py.objetivo'
            )
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }


        DB::connection('poa_prod')
            ->table('proyectos')
            ->where('proyecto_id', $id)
            ->update([
                'nombre' => $request->has('proyecto_nombre') ? trim($request->proyecto_nombre) : $proyecto->nombre,
                'responsable_ficha' => $request->has('responsable_ficha') ? trim($request->responsable_ficha) : $proyecto->responsable_ficha,
                'puesto_responsable_ficha' => $request->has('puesto_responsable_ficha') ? trim($request->puesto_responsable_ficha) : $proyecto->puesto_responsable_ficha,
                'autorizante_nombre' => $request->has('autorizante_nombre') ? trim($request->autorizante_nombre) : $proyecto->autorizante_nombre,
                'autorizante_puesto' => $request->has('autorizante_puesto') ? trim($request->autorizante_puesto) : $proyecto->autorizante_puesto,
                'justificacion' => $request->has('justificacion') ? trim($request->justificacion) : $proyecto->justificacion,
                'descripcion' => $request->has('descripcion') ? trim($request->descripcion) : $proyecto->descripcion,
                'objetivo' => $request->has('objetivo') ? trim($request->objetivo) : $proyecto->objetivo,
            ]);

        return response()->json(['message' => 'Datos del proyecto actualizados correctamente']);
    }

    public function getBitacora($id)
    {
        $bitacora = DB::table('proyecto_bitacoras')
            ->where('proyecto_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $userIds = $bitacora->pluck('user_id')->filter()->unique();
        $users = [];
        if ($userIds->isNotEmpty()) {
            $users = DB::connection('poa_prod')->table('usuarios_poa')
                ->whereIn('usuario_poa_id', $userIds)
                ->select('usuario_poa_id', 'nombre', 'apellido_paterno', 'apellido_materno')
                ->get()
                ->keyBy('usuario_poa_id');
        }

        $bitacora->transform(function ($item) use ($users) {
            if ($item->user_id && isset($users[$item->user_id])) {
                $u = $users[$item->user_id];
                $item->usuario = trim($u->nombre . ' ' . $u->apellido_paterno . ' ' . $u->apellido_materno);
            } else {
                $item->usuario = 'Sistema';
            }
            return $item;
        });

        return response()->json($bitacora);
    }

    public function agregarNotaBitacora(Request $request, $id)
    {
        $request->validate([
            'mensaje' => 'required|string',
            'accion' => 'required|string'
        ]);

        $user = $request->user();
        
        $status = 'Captura';
        if ($request->accion === 'enviar_validador') {
            $status = 'Validacion';
        } elseif ($request->accion === 'regresar_capturador') {
            $status = 'Captura'; // regresa al capturador
        } elseif ($request->accion === 'enviar_dpyrf') {
            $status = 'Cerrada'; // enviado a DPyRF
        }

        $updateData = ['status' => $status];
        if ($status === 'Cerrada') {
            $updateData['fecha_cierre'] = now();
        }
        
        // Clear verification date if the status is no longer Verificado
        if ($status !== 'Verificado') {
            $updateData['fecha_verificacion'] = null;
        }

        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $id)
            ->update($updateData);

        DB::table('proyecto_bitacoras')->insert([
            'proyecto_id' => $id,
            'user_id' => $user->usuario_poa_id,
            'accion' => $request->accion,
            'mensaje' => $request->mensaje,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->checkAndSendEmail($id, $status, $request->mensaje);

        return response()->json(['message' => 'Nota agregada correctamente y estatus actualizado']);
    }

    public function cambiarEstatus(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required|string',
        ]);

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->select('py.proyecto_id', 'py.status')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado o no tienes permisos'], 404);
        }
        
        $oldStatus = $proyecto->status;
        $newStatus = $request->estatus;

        DB::connection('poa_prod')
            ->table('proyectos')
            ->where('proyecto_id', $id)
            ->update([
                'status' => $newStatus,
                'fecha_verificacion' => $newStatus === 'Verificado' ? now() : null
            ]);

        \Carbon\Carbon::setLocale('es');
        $fecha = now()->translatedFormat('d \d\e F \d\e Y H:i \h\r\s');
        $mensaje = "Cambio de estatus ($oldStatus por $newStatus) por el administrador. $fecha";

        DB::table('proyecto_bitacoras')->insert([
            'proyecto_id' => $id,
            'user_id' => $request->user()->usuario_poa_id ?? null,
            'accion' => 'cambio_estatus_admin',
            'mensaje' => $mensaje,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->checkAndSendEmail($id, $newStatus, $mensaje);

        return response()->json(['message' => 'Estatus cambiado exitosamente']);
    }

    public function verificar(Request $request, $id)
    {
        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->select('py.proyecto_id', 'py.status')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado o no tienes permisos'], 404);
        }

        DB::connection('poa_prod')
            ->table('proyectos')
            ->where('proyecto_id', $id)
            ->update([
                'status' => 'Verificado',
                'fecha_verificacion' => now()
            ]);

        $this->checkAndSendEmail($id, 'Verificado', 'El proyecto ha sido Verificado por la DPyRF y está listo para su impresión, firma y entrega impresa al área correspondiente.');

        DB::table('proyecto_bitacoras')->insert([
            'proyecto_id' => $id,
            'user_id' => $request->user()->usuario_poa_id ?? null,
            'accion' => 'verificar_proyecto',
            'mensaje' => 'Proyecto marcado como Verificado.',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Proyecto verificado exitosamente']);
    }

    private function checkAndSendEmail($proyectoId, $status, $mensajeBitacora) {
        if ($status !== 'Verificado') {
            return;
        }

        try {
            $config = \App\Models\MailingConfig::first();
            if (!$config || !$config->host) return;

            \Illuminate\Support\Facades\Config::set('mail.default', 'smtp');
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.host', $config->host);
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.port', $config->port);
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.encryption', $config->encryption);
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.username', $config->username);
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.password', $config->password);
            \Illuminate\Support\Facades\Config::set('mail.from.address', $config->from_address);
            \Illuminate\Support\Facades\Config::set('mail.from.name', $config->from_name);

            $urgInfo = DB::connection('poa_prod')
                ->table('proyectos as py')
                ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
                ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
                ->select('urg.unidad_responsable_gasto_id', 'py.numero', 'py.nombre', 'py.status')
                ->where('py.proyecto_id', $proyectoId)
                ->first();

            if (!$urgInfo) return;
            
            $proyectoObj = (object)[
                'numero' => $urgInfo->numero,
                'nombre' => $urgInfo->nombre,
                'status' => $status
            ];

            // Buscar usuarios con rol Validador o Capturador en la URG de este proyecto
            $validadores = \App\Models\User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['Validador', 'Capturador']);
                })
                ->whereHas('unidadesResponsables', function($q) use ($urgInfo) {
                    $q->where('unidades_responsables_gastos.unidad_responsable_gasto_id', $urgInfo->unidad_responsable_gasto_id);
                })
                ->whereNotNull('correo')
                ->get();

            \Illuminate\Support\Facades\Mail::purge('smtp');

            foreach($validadores as $validador) {
                if ($validador->correo) {
                    \Illuminate\Support\Facades\Mail::mailer('smtp')->to($validador->correo)->send(new \App\Mail\ProyectoNotificacionMail($proyectoObj, $mensajeBitacora));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $isAdministrador = $user && ($user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->roles->pluck('name')->contains('Administrador'));
        
        if (!$isAdministrador) {
            return response()->json(['message' => 'Solo los Administradores pueden eliminar proyectos.'], 403);
        }

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select('py.proyecto_id', 'py.numero', 'urg.ejercicio_id')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado o no tienes permisos para eliminarlo'], 404);
        }

        DB::connection('poa_prod')->beginTransaction();
        try {
            // Delete related records to maintain integrity
            $pid = $id;

            // Delete metas related data
            $metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $pid)->get();
            foreach ($metas as $meta) {
                $mid = $meta->meta_id;
                DB::connection('poa_prod')->table('meses_metas_programadas')->where('meta_id', $mid)->delete();
                DB::connection('poa_prod')->table('meses_metas_alcanzadas')->where('meta_id', $mid)->delete();
                DB::connection('poa_prod')->table('indicadores')->where('meta_id', $mid)->delete();
            }
            DB::connection('poa_prod')->table('metas')->where('proyecto_id', $pid)->delete();
            
            // Delete other project related data
            
            DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $pid)->delete();
            DB::connection('poa_prod')->table('acciones_sustantivas_derechos_humanos')->where('proyecto_id', $pid)->delete();
            DB::connection('poa_prod')->table('equidades_generos')->where('proyecto_id', $pid)->delete();
            DB::connection('poa_prod')->table('meses_proyectos')->where('proyecto_id', $pid)->delete();
            DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->where('proyecto_id', $pid)->delete();
            DB::connection('poa_prod')->table('proyecto_bitacoras')->where('proyecto_id', $pid)->delete();
            
            // Finally delete project itself
            DB::connection('poa_prod')
                ->table('proyectos')
                ->where('proyecto_id', $pid)
                ->delete();

            // Recorrer los números de los proyectos restantes del mismo ejercicio (global)
            $pys = DB::connection('poa_prod')->table('proyectos as py')
                ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
                ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
                ->where('urg.ejercicio_id', $proyecto->ejercicio_id)
                ->whereRaw('CAST(py.numero AS INTEGER) > ?', [(int)$proyecto->numero])
                ->orderByRaw('CAST(py.numero AS INTEGER) asc')
                ->select('py.proyecto_id', 'py.numero')
                ->get();
                
            foreach ($pys as $p) {
                $nuevoNumero = intval($p->numero) - 1;
                DB::connection('poa_prod')->table('proyectos')
                    ->where('proyecto_id', $p->proyecto_id)
                    ->update(['numero' => str_pad($nuevoNumero, 2, '0', STR_PAD_LEFT)]);
            }

            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Proyecto eliminado y numeración actualizada correctamente']);
        } catch (\Exception $e) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'Error al eliminar el proyecto', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateAlineacionPei(Request $request, $id)
    {
        $request->validate([
            'objetivos_estrategicos' => 'required|array|min:1',
            'objetivos_estrategicos.*' => 'integer',
        ]);

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->select('py.proyecto_id', 'ej.ejercicio as ejercicio_anio')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();

        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $peiPrograma = $this->getPeiProgramByYear((int) $proyecto->ejercicio_anio);
        if (!$peiPrograma) {
            return response()->json(['message' => 'No existe PEI para el ejercicio del proyecto'], 422);
        }

        $objetivos = DB::table('pei_objetivos_estrategicos')
            ->whereIn('pei_objetivo_estrategico_id', $request->objetivos_estrategicos)
            ->get();

        if ($objetivos->count() !== count(array_unique($request->objetivos_estrategicos))) {
            return response()->json(['message' => 'Uno o más objetivos estratégicos no son válidos'], 422);
        }

        // Validate that all lines belong to the current PEI program
        $lineasIds = $objetivos->pluck('pei_linea_estrategica_id')->unique();
        $lineasCount = DB::table('pei_lineas_estrategicas')
            ->whereIn('pei_linea_estrategica_id', $lineasIds)
            ->where('pei_programa_id', $peiPrograma->pei_programa_id)
            ->count();

        if ($lineasCount !== $lineasIds->count()) {
            return response()->json(['message' => 'Una o más líneas estratégicas no pertenecen al PEI del ejercicio actual'], 422);
        }

        DB::transaction(function () use ($id, $peiPrograma, $objetivos) {
            DB::connection('poa_prod')->table('pei_proyecto_alineaciones')
                ->where('proyecto_id', $id)
                ->delete();

            $inserts = [];
            foreach ($objetivos as $obj) {
                $inserts[] = [
                    'proyecto_id' => $id,
                    'pei_programa_id' => $peiPrograma->pei_programa_id,
                    'pei_linea_estrategica_id' => $obj->pei_linea_estrategica_id,
                    'pei_objetivo_estrategico_id' => $obj->pei_objetivo_estrategico_id,
                ];
            }
            DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->insert($inserts);
        });

        return response()->json(['message' => 'Alineación PEI actualizada correctamente']);
    }

    public function storeMeta(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:512',
            'unidad_medida_id' => 'required|integer',
            'tipo' => 'required|in:principal,complementaria',
            'orden' => 'nullable|integer|min:0',
            'peso' => 'nullable|numeric',
            'tmc' => 'nullable|integer',
            'meses' => 'required|array',
            'meses.*' => 'numeric',
        ]);

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select('py.proyecto_id', 'urg.ejercicio_id')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();
        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $unidadValida = DB::connection('poa_prod')
            ->table('unidades_medidas')
            ->where('unidad_medida_id', $request->unidad_medida_id)
            ->where('ejercicio_id', $proyecto->ejercicio_id)
            ->first();

        if (!$unidadValida) {
            return response()->json(['message' => 'La unidad de medida no pertenece al ejercicio del proyecto'], 422);
        }

        $maxOrden = DB::connection('poa_prod')
            ->table('metas')
            ->where('proyecto_id', $id)
            ->where('tipo', $request->tipo)
            ->max('orden');

        DB::connection('poa_prod')->beginTransaction();

        try {
            $metaId = DB::connection('poa_prod')
                ->table('metas')
                ->insertGetId([
                    'proyecto_id' => $id,
                    'unidad_medida_id' => $request->unidad_medida_id,
                    'meta_padre_id' => $request->meta_id ?? null,
                    'tipo' => $request->tipo,
                    'orden' => $request->orden ?? (($maxOrden ?? 0) + 1),
                    'nombre' => $request->nombre,
                    'peso' => $request->input('peso', 0),
                    'tmc' => $request->input('tmc', 1),
                ]);

            for ($mesId = 1; $mesId <= 12; $mesId++) {
                $valor = (float) ($request->input("meses.$mesId", 0));
                DB::connection('poa_prod')
                    ->table('meses_metas_programadas')
                    ->insert([
                        'meta_id' => $metaId,
                        'mes_id' => $mesId,
                        'numero' => $valor,
                    ]);
            }

            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Meta agregada correctamente']);
        } catch (\Throwable $throwable) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'No fue posible agregar la meta', 'error' => $throwable->getMessage()], 500);
        }
    }

    public function updateMeta(Request $request, $id, $metaId)
    {
        $request->validate([
            'nombre' => 'required|string|max:512',
            'unidad_medida_id' => 'required|integer',
            'tipo' => 'required|in:principal,complementaria',
            'orden' => 'nullable|integer|min:0',
            'peso' => 'nullable|numeric',
            'tmc' => 'nullable|integer',
            'meses' => 'required|array',
            'meses.*' => 'numeric',
        ]);

        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select('py.proyecto_id', 'urg.ejercicio_id as ejercicio_id')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();
        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $unidadValida = DB::connection('poa_prod')
            ->table('unidades_medidas')
            ->where('unidad_medida_id', $request->unidad_medida_id)
            ->where('ejercicio_id', $proyecto->ejercicio_id)
            ->first();

        if (!$unidadValida) {
            return response()->json(['message' => 'La unidad de medida no pertenece al ejercicio del proyecto'], 422);
        }

        $meta = DB::connection('poa_prod')
            ->table('metas')
            ->where('meta_id', $metaId)
            ->where('proyecto_id', $id)
            ->first();

        if (!$meta) {
            return response()->json(['message' => 'Meta no encontrada'], 404);
        }

        DB::connection('poa_prod')->beginTransaction();

        try {
            DB::connection('poa_prod')
                ->table('metas')
                ->where('meta_id', $metaId)
                ->update([
                    'unidad_medida_id' => $request->unidad_medida_id,
                    'tipo' => $request->tipo,
                    'orden' => $request->orden ?? $meta->orden,
                    'nombre' => $request->nombre,
                    'peso' => $request->input('peso', 0),
                    'tmc' => $request->input('tmc', 1),
                ]);

            DB::connection('poa_prod')
                ->table('meses_metas_programadas')
                ->where('meta_id', $metaId)
                ->delete();

            for ($mesId = 1; $mesId <= 12; $mesId++) {
                $valor = (float) ($request->input("meses.$mesId", 0));
                DB::connection('poa_prod')
                    ->table('meses_metas_programadas')
                    ->insert([
                        'meta_id' => $metaId,
                        'mes_id' => $mesId,
                        'numero' => $valor,
                    ]);
            }

            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Meta actualizada correctamente']);
        } catch (\Throwable $throwable) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'No fue posible actualizar la meta', 'error' => $throwable->getMessage()], 500);
        }
    }

    public function destroyMeta(Request $request, $id, $metaId)
    {
        $query = DB::connection('poa_prod')
            ->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->select('py.proyecto_id')
            ->where('py.proyecto_id', $id);

        $query = $this->applyAccessScope($query, $request->user());
        $proyecto = $query->first();
        if (!$proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $meta = DB::connection('poa_prod')
            ->table('metas')
            ->where('meta_id', $metaId)
            ->where('proyecto_id', $id)
            ->first();

        if (!$meta) {
            return response()->json(['message' => 'Meta no encontrada'], 404);
        }

        DB::connection('poa_prod')->beginTransaction();

        try {
            DB::connection('poa_prod')
                ->table('meses_metas_programadas')
                ->where('meta_id', $metaId)
                ->delete();

            DB::connection('poa_prod')
                ->table('meses_metas_alcanzadas')
                ->where('meta_id', $metaId)
                ->delete();

            DB::connection('poa_prod')
                ->table('indicadores')
                ->where('meta_id', $metaId)
                ->delete();

            DB::connection('poa_prod')
                ->table('metas')
                ->where('meta_id', $metaId)
                ->delete();

            if ($meta->tipo === 'complementaria') {
                DB::connection('poa_prod')
                    ->table('metas')
                    ->where('proyecto_id', $id)
                    ->where('tipo', 'complementaria')
                    ->where('orden', '>', $meta->orden)
                    ->decrement('orden');
            }

            DB::connection('poa_prod')->commit();
            return response()->json(['message' => 'Meta eliminada correctamente']);
        } catch (\Throwable $throwable) {
            DB::connection('poa_prod')->rollBack();
            return response()->json(['message' => 'No fue posible eliminar la meta', 'error' => $throwable->getMessage()], 500);
        }
    }
}
