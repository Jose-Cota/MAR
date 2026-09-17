<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AvanceTrimestralExport;
use App\Exports\AperturaProgramaticaExport;
use App\Exports\MatrizMetasExport;
use App\Exports\AvanceMensualExport;
use App\Exports\IndicadoresExport;
use App\Exports\TablaProyectosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function descargarAperturaProgramatica(Request $request)
    {
        $ejercicio = $request->query('ejercicio', date('Y'));
        
        $nombreArchivo = 'Apertura_Programatica_POA_' . $ejercicio . '.xlsx';
        
        return Excel::download(new AperturaProgramaticaExport($ejercicio), $nombreArchivo);
    }
    public function descargarAvanceTrimestral(Request $request)
    {
        $ejercicio = $request->query('ejercicio', date('Y'));
        $trimestre = $request->query('trimestre');
        
        $nombreArchivo = 'Avance_Trimestral_POA_' . $ejercicio . '.xlsx';
        
        return Excel::download(new AvanceTrimestralExport($ejercicio, $trimestre), $nombreArchivo);
    }
    public function seguimientoAvances(Request $request)
    {
        $ejercicio = $request->query('ejercicio');
        $mesNombre = $request->query('mes', 'Enero');
        
        $mesesMap = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];
        
        $mesId = $mesesMap[$mesNombre] ?? 1;

        Log::info("API params: ejercicio={$ejercicio}, mes={$mesNombre}, mesId={$mesId}");

        $query = "
            SELECT 
                urg.numero as urg,
                MAX(urg.nombre) as urg_nombre,
                py.numero as py,
                MAX(py.nombre) as py_nombre,
                SUM(
                    COALESCE((
                        SELECT SUM(mmp.numero)
                        FROM metas m
                        JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
                        WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mmp.mes_id <= ?
                    ), 0)
                ) as total_programado,
                SUM(
                    COALESCE((
                        SELECT SUM(mma.numero)
                        FROM metas m
                        JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id
                        WHERE m.proyecto_id = py.proyecto_id AND m.tipo = 'principal' AND mma.mes_id <= ?
                    ), 0)
                ) as total_alcanzado
            FROM proyectos as py
            JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
            JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
            JOIN ejercicios as ej ON urg.ejercicio_id = ej.ejercicio_id
            WHERE ej.ejercicio = ?
            AND UPPER(TRIM(py.nombre)) != 'BAJA'
            GROUP BY urg.numero, py.numero, py.proyecto_id
            ORDER BY urg.numero, py.numero
        ";
        
        // Validar si existen proyectos para el año solicitado en PROD
        $count = DB::connection('poa_prod')->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->where('ej.ejercicio', $ejercicio)
            ->count();

        Log::info("API count: {$count}");

        if ($count === 0) {
            return response()->json([]);
        }

        $proyectos = DB::connection('poa_prod')->select($query, [$mesId, $mesId, $ejercicio]);
        
        $groupedData = [];
        foreach ($proyectos as $p) {
            $label = sprintf("%02s-%02s", $p->urg, $p->py);
            if (!isset($groupedData[$label])) {
                $groupedData[$label] = [
                    'clave' => $label,
                    'nombre' => $p->py_nombre,
                    'total_programado' => 0,
                    'total_alcanzado' => 0,
                    'urg_numero' => $p->urg,
                    'urg_nombre' => $p->urg_nombre,
                ];
            }
            $groupedData[$label]['total_programado'] += $p->total_programado;
            $groupedData[$label]['total_alcanzado'] += $p->total_alcanzado;
        }
        
        $data = [];
        foreach ($groupedData as $label => $g) {
            $avance = 0;
            if ($g['total_programado'] > 0) {
                $avance = ($g['total_alcanzado'] / $g['total_programado']);
            } else {
                $avance = ($g['total_alcanzado'] > 0) ? 100 : 0; 
            }
            
            $data[] = [
                'clave' => $g['clave'],
                'nombre' => $g['nombre'],
                'avance' => round($avance, 2),
                'urg_numero' => $g['urg_numero'],
                'urg_nombre' => $g['urg_nombre'],
            ];
        }
        
        // Ordenar por clave para mantener el orden
        usort($data, function($a, $b) {
            return strcmp($a['clave'], $b['clave']);
        });

        return response()->json($data);
    }

    public function getDetalleMetasProyecto(Request $request)
    {
        $proyecto_id = $request->query('proyecto_id');
        if (!$proyecto_id) return response()->json(['error' => 'Falta proyecto_id'], 400);

        $metas = DB::connection('poa_prod')->table('metas')
            ->leftJoin('unidades_medidas', 'metas.unidad_medida_id', '=', 'unidades_medidas.unidad_medida_id')
            ->select('metas.meta_id', 'metas.tipo', 'metas.nombre as meta', 'unidades_medidas.nombre as unidad_medida')
            ->where('metas.proyecto_id', $proyecto_id)
            ->get();

        $metaIds = $metas->pluck('meta_id')->toArray();

        $programadasList = DB::connection('poa_prod')->table('meses_metas_programadas')
            ->whereIn('meta_id', $metaIds)
            ->get();
            
        $alcanzadasList = DB::connection('poa_prod')->table('meses_metas_alcanzadas')
            ->whereIn('meta_id', $metaIds)
            ->get();

        $progByMeta = [];
        foreach($programadasList as $p) {
            $progByMeta[$p->meta_id][$p->mes_id] = $p->numero;
        }

        $alcByMeta = [];
        foreach($alcanzadasList as $a) {
            $alcByMeta[$a->meta_id][$a->mes_id] = $a->numero;
        }

        $result = [];
        foreach ($metas as $meta) {
            $programadas = $progByMeta[$meta->meta_id] ?? [];
            $alcanzadas = $alcByMeta[$meta->meta_id] ?? [];

            $meses = [];
            $acumulado_prog = 0;
            $acumulado_alc = 0;

            for ($i = 1; $i <= 12; $i++) {
                $prog = (float)($programadas[$i] ?? 0);
                $alc = (float)($alcanzadas[$i] ?? 0);
                
                $acumulado_prog += $prog;
                $acumulado_alc += $alc;

                $porcentaje_mes = $prog > 0 ? ($alc / $prog) * 100 : ($alc > 0 ? 100 : 0);
                $porcentaje_acumulado = $acumulado_prog > 0 ? ($acumulado_alc / $acumulado_prog) * 100 : ($acumulado_alc > 0 ? 100 : 0);

                $meses[$i] = [
                    'programado' => $prog,
                    'alcanzado' => $alc,
                    'porcentaje_mes' => round($porcentaje_mes, 1),
                    'porcentaje_acumulado' => round($porcentaje_acumulado, 1),
                ];
            }

            $metaArray = (array)$meta;
            $metaArray['meses'] = $meses;
            $result[] = $metaArray;
        }

        return response()->json($result);
    }

    public function descargarAvanceMensual(Request $request, $proyecto_id)
    {
        $proyecto = DB::connection('poa_prod')->table('proyectos')
            ->select('numero', 'nombre')
            ->where('proyecto_id', $proyecto_id)
            ->first();
            
        if (!$proyecto) return response()->json(['error' => 'Proyecto no encontrado'], 404);

        // Fetch detailed metas just like getDetalleMetasProyecto
        $request->merge(['proyecto_id' => $proyecto_id]);
        $response = $this->getDetalleMetasProyecto($request);
        $metas = json_decode($response->getContent(), true);

        $datos = [
            'numero' => $proyecto->numero,
            'nombre' => $proyecto->nombre,
            'metas' => $metas
        ];

        $nombreArchivo = 'Avance_Mensual_Proyecto_' . $proyecto->numero . '.xlsx';
        return Excel::download(new AvanceMensualExport($datos), $nombreArchivo);
    }

    public function descargarMatrizMetas(Request $request)
    {
        $ejercicio = $request->query('ejercicio', date('Y'));
        
        $proyectos = DB::connection('poa_prod')
            ->table('metas as m')
            ->join('proyectos as py', 'm.proyecto_id', '=', 'py.proyecto_id')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->join('unidades_medidas as um', 'm.unidad_medida_id', '=', 'um.unidad_medida_id')
            ->where('ej.ejercicio', $ejercicio)
            ->where('m.tipo', 'principal')
            ->select(
                'm.meta_id', 'm.nombre as meta',
                'py.numero as proyecto',
                'sp.numero as subprograma',
                'pg.numero as programa',
                'urg.numero as urg',
                'um.nombre as unidad_medida'
            )
            ->orderBy('urg.numero')
            ->orderBy('pg.numero')
            ->orderBy('sp.numero')
            ->orderBy('py.numero')
            ->get();

        $metaIds = $proyectos->pluck('meta_id')->toArray();

        $programadasList = DB::connection('poa_prod')->table('meses_metas_programadas')
            ->whereIn('meta_id', $metaIds)->get();
            
        $alcanzadasList = DB::connection('poa_prod')->table('meses_metas_alcanzadas')
            ->whereIn('meta_id', $metaIds)->get();

        $progByMeta = [];
        foreach($programadasList as $p) $progByMeta[$p->meta_id][$p->mes_id] = $p->numero;

        $alcByMeta = [];
        foreach($alcanzadasList as $a) $alcByMeta[$a->meta_id][$a->mes_id] = $a->numero;

        $result = [];
        foreach ($proyectos as $p) {
            $programadas = $progByMeta[$p->meta_id] ?? [];
            $alcanzadas = $alcByMeta[$p->meta_id] ?? [];

            $meses = [];
            for ($i = 1; $i <= 12; $i++) {
                $meses[$i] = [
                    'programado' => (float)($programadas[$i] ?? 0),
                    'alcanzado' => (float)($alcanzadas[$i] ?? 0)
                ];
            }

            $pArray = (array)$p;
            $pArray['meses'] = $meses;
            $result[] = $pArray;
        }

        $nombreArchivo = 'Matriz_Metas_POA_' . $ejercicio . '.xlsx';
        return Excel::download(new MatrizMetasExport($result), $nombreArchivo);
    }

    public function descargarIndicadores(Request $request)
    {
        $ejercicio = $request->query('ejercicio', date('Y'));
        
        $indicadores = DB::connection('poa_prod')
            ->table('indicadores as ind')
            ->join('proyectos as py', 'ind.proyecto_id', '=', 'py.proyecto_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
            ->leftJoin('frecuencias as fr', 'ind.frecuencia_id', '=', 'fr.frecuencia_id')
            ->leftJoin('unidades_medidas as um', 'ind.unidad_medida_id', '=', 'um.unidad_medida_id')
            ->where('ej.ejercicio', $ejercicio)
            ->select(
                'ind.nombre', 'ind.definicion', 'ind.metodo_calculo', 'ind.meta',
                'py.numero as proyecto_numero', 'py.nombre as proyecto_nombre',
                'urg.numero as urg_numero', 'urg.nombre as urg_nombre',
                'fr.nombre as frecuencia',
                'um.nombre as unidad_medida'
            )
            ->orderBy('urg.numero')
            ->orderBy('py.numero')
            ->get();

        $nombreArchivo = 'Reporte_Indicadores_' . $ejercicio . '.xlsx';
        return Excel::download(new IndicadoresExport($indicadores), $nombreArchivo);
    }

    public function tablaProyectos(Request $request)
    {
        $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
        if (!$ejercicio) {
            return response()->json(['error' => 'Ejercicio 2027 no encontrado'], 404);
        }

        $proyectosRaw = DB::connection('poa_prod')->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->leftJoin('subprogramas as sp', 'p.subprograma_id', '=', 'sp.subprograma_id')
            ->leftJoin('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->where('p.ejercicio_id', $ejercicio->ejercicio_id)
            ->select(
                'urg.numero as ur',
                'ro.numero as ro',
                'pg.numero as pg',
                'sp.numero as sp',
                'p.numero as py',
                'p.nombre as denominacion_proyecto'
            )
            ->orderBy('urg.numero')
            ->orderBy('ro.numero')
            ->orderBy('pg.numero')
            ->orderBy('sp.numero')
            ->orderBy('p.numero')
            ->get();

        $pdf = Pdf::loadView('reportes.tabla_proyectos', ['proyectos' => $proyectosRaw])
                  ->setPaper('a4', 'portrait')
                  ->setOption('isPhpEnabled', true);
        
        return $pdf->stream('Tabla_de_Proyectos_2027.pdf');
    }

    public function recursosAsociadosPdf(Request $request)
    {
        $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
        if (!$ejercicio) {
            return response()->json(['error' => 'Ejercicio 2027 no encontrado'], 404);
        }

        $proyectosRaw = DB::connection('poa_prod')->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->where('p.ejercicio_id', $ejercicio->ejercicio_id)
            ->select(
                'urg.numero as ur_numero',
                'urg.nombre as ur_nombre',
                'p.proyecto_id',
                'p.numero as py_numero',
                'p.nombre as py_nombre'
            )
            ->orderBy('urg.numero')
            ->orderBy('p.numero')
            ->get();

        $actividades = DB::connection('poa_prod')->table('actividades_sustantivas')
            ->whereIn('proyecto_id', $proyectosRaw->pluck('proyecto_id'))
            ->select('proyecto_id', 'descripcion', 'recursos_asociados')
            ->orderBy('numero')
            ->get();
            
        $datosAgrupados = [];
        foreach ($proyectosRaw as $proy) {
            $urKey = $proy->ur_numero . ' ' . $proy->ur_nombre;
            if (!isset($datosAgrupados[$urKey])) {
                $datosAgrupados[$urKey] = [];
            }
            
            $recursos = $actividades->where('proyecto_id', $proy->proyecto_id)
                                    ->whereNotNull('recursos_asociados')
                                    ->filter(function($act) { return trim($act->recursos_asociados) !== ''; })
                                    ->map(function($act) {
                                        return [
                                            'actividad' => $act->descripcion,
                                            'recurso' => $act->recursos_asociados
                                        ];
                                    })
                                    ->values()
                                    ->toArray();
                                    
            if (count($recursos) > 0) {
                $datosAgrupados[$urKey][] = [
                    'numero' => $proy->py_numero,
                    'nombre' => $proy->py_nombre,
                    'recursos' => $recursos
                ];
            }
        }

        // Eliminar URs sin proyectos con recursos
        $datosAgrupados = array_filter($datosAgrupados, function($proyectos) {
            return count($proyectos) > 0;
        });

        $logoPath = base_path('../frontend/src/assets/logo_tecdmx.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('reportes.recursos_asociados_pdf', [
            'datosAgrupados' => $datosAgrupados,
            'logo' => $logoBase64
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isPhpEnabled', true);
        
        return $pdf->stream('Recursos_Asociados_2027.pdf');
    }

    public function tablaProyectosExcel(Request $request)
    {
        $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
        if (!$ejercicio) {
            return response()->json(['error' => 'Ejercicio 2027 no encontrado'], 404);
        }

        $proyectosRaw = DB::connection('poa_prod')->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->join('actividades_sustantivas as a', 'p.proyecto_id', '=', 'a.proyecto_id')
            ->where('p.ejercicio_id', $ejercicio->ejercicio_id)
            ->whereNotNull('a.recursos_asociados')
            ->whereRaw("TRIM(a.recursos_asociados) != ''")
            ->select(
                'p.numero as py_numero',
                'p.nombre as py_nombre',
                'a.descripcion as actividad_sustantiva',
                'a.recursos_asociados'
            )
            ->orderBy('urg.numero')
            ->orderBy('p.numero')
            ->orderBy('a.numero')
            ->get();

        return Excel::download(new TablaProyectosExport($proyectosRaw), 'Tabla_de_Proyectos_2027.xlsx');
    }
}
