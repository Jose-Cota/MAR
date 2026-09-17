<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ConfiguracionController extends Controller
{


    /**
     * Get all Ejercicios for catalog dropdowns
     */
    public function getEjerciciosCatalog()
    {
        $ejercicios = DB::table('ejercicios')
            ->orderBy('ejercicio', 'desc')
            ->get();
            
        return response()->json($ejercicios);
    }

    /**
     * Get Elaboración config for a given year
     */
    public function getElaboracion($ejercicioId)
    {
        $ejercicio = DB::table('ejercicios')->where('ejercicio_id', $ejercicioId)->first();
        if (!$ejercicio) {
            return response()->json(['message' => 'Ejercicio no encontrado'], 404);
        }

        $operacion = DB::table('operaciones_ejercicios')
            ->where('ejercicio_id', $ejercicioId)
            ->where('tipo', 'elaboracion_proyectos')
            ->first();

        return response()->json([
            'habilitado' => $operacion ? $operacion->habilitado === 'si' : false,
            'permitir_edicion' => $ejercicio->permitir_edicion_elaboracion === 'si'
        ]);
    }

    /**
     * Save Elaboración config for a given year
     */
    public function saveElaboracion(Request $request, $ejercicioId)
    {
        $request->validate([
            'habilitado' => 'required|boolean',
            'permitir_edicion' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            DB::table('ejercicios')->where('ejercicio_id', $ejercicioId)->update([
                'permitir_edicion_elaboracion' => $request->permitir_edicion ? 'si' : 'no'
            ]);

            DB::table('operaciones_ejercicios')->updateOrInsert(
                ['ejercicio_id' => $ejercicioId, 'tipo' => 'elaboracion_proyectos'],
                ['habilitado' => $request->habilitado ? 'si' : 'no']
            );

            DB::commit();
            return response()->json(['message' => 'Configuración de elaboración guardada correctamente.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar configuración', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Seguimiento config for a given year
     */
    public function getSeguimiento($ejercicioId)
    {
        $ejercicio = DB::table('ejercicios')->where('ejercicio_id', $ejercicioId)->first();
        if (!$ejercicio) {
            return response()->json(['message' => 'Ejercicio no encontrado'], 404);
        }

        $operacion = DB::table('operaciones_ejercicios')
            ->where('ejercicio_id', $ejercicioId)
            ->where('tipo', 'seguimiento_proyectos')
            ->first();

        $meses = DB::table('meses_controles_metas')
            ->where('ejercicio_id', $ejercicioId)
            ->get();

        return response()->json([
            'habilitado' => $operacion ? $operacion->habilitado === 'si' : false,
            'tipo_captura_seguimiento' => $ejercicio->tipo_captura_seguimiento,
            'ultimo_mes_visible' => $ejercicio->ultimo_mes_visible,
            'ultimo_mes_consulta' => $ejercicio->ultimo_mes_consulta,
            'meses_habilitados' => $meses->pluck('habilitado', 'mes_id')->map(function($val) {
                return $val === 'si';
            })
        ]);
    }

    /**
     * Save Seguimiento config for a given year
     */
    public function saveSeguimiento(Request $request, $ejercicioId)
    {
        $request->validate([
            'habilitado' => 'required|boolean',
            'tipo_captura_seguimiento' => 'required|string',
            'ultimo_mes_visible' => 'nullable|string',
            'ultimo_mes_consulta' => 'nullable|string',
            'meses_habilitados' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            DB::table('ejercicios')->where('ejercicio_id', $ejercicioId)->update([
                'tipo_captura_seguimiento' => $request->tipo_captura_seguimiento,
                'ultimo_mes_visible' => $request->ultimo_mes_visible,
                'ultimo_mes_consulta' => $request->ultimo_mes_consulta
            ]);

            DB::table('operaciones_ejercicios')->updateOrInsert(
                ['ejercicio_id' => $ejercicioId, 'tipo' => 'seguimiento_proyectos'],
                ['habilitado' => $request->habilitado ? 'si' : 'no']
            );

            // Update months
            foreach ($request->meses_habilitados as $mesId => $habilitado) {
                DB::table('meses_controles_metas')->updateOrInsert(
                    ['ejercicio_id' => $ejercicioId, 'mes_id' => $mesId],
                    ['habilitado' => $habilitado ? 'si' : 'no']
                );
            }

            DB::commit();
            return response()->json(['message' => 'Configuración de seguimiento guardada correctamente.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar configuración', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Anteproyecto config
     */
    public function getAnteproyecto($ejercicioId)
    {
        $operacion = DB::table('operaciones_ejercicios')
            ->where('ejercicio_id', $ejercicioId)
            ->where('tipo', 'anteproyecto')
            ->first();

        return response()->json([
            'habilitado' => $operacion ? $operacion->habilitado === 'si' : false,
        ]);
    }

    /**
     * Save Anteproyecto / Generate new exercise
     */
    public function generarAnteproyecto(Request $request, $ejercicioId)
    {
        try {
            // Check if the current exercise exists
            $ejercicioActual = DB::table('ejercicios')->where('ejercicio_id', $ejercicioId)->first();
            if (!$ejercicioActual) {
                throw new Exception("El ejercicio actual no existe.");
            }

            $nuevoAnio = $ejercicioActual->ejercicio + 1;
            
            DB::beginTransaction();

            // 1. Create or get new Ejercicio
            $nuevoEjercicio = DB::table('ejercicios')->where('ejercicio', $nuevoAnio)->first();
            if (!$nuevoEjercicio) {
                $newEjId = DB::table('ejercicios')->insertGetId([
                    'ejercicio' => $nuevoAnio,
                    'permitir_edicion_seguimiento' => 'no',
                    'permitir_edicion_seguimiento_derechos_humanos' => 'no',
                    'permitir_edicion_elaboracion' => 'no',
                    'ultimo_mes_visible' => 'enero',
                    'ultimo_mes_consulta' => 'enero',
                    'tipo_captura_seguimiento' => 'global'
                ]);
            } else {
                $newEjId = $nuevoEjercicio->ejercicio_id;
            }

            DB::table('operaciones_ejercicios')->updateOrInsert(
                ['ejercicio_id' => $newEjId, 'tipo' => 'anteproyecto'],
                ['habilitado' => 'si']
            );

            // Copy 'unidades_responsables_gastos'
            $urgs = DB::table('unidades_responsables_gastos')->where('ejercicio_id', $ejercicioId)->get();
            $urgMap = []; // Old ID => New ID
            foreach ($urgs as $urg) {
                $newUrgId = DB::table('unidades_responsables_gastos')->insertGetId([
                    'ejercicio_id' => $newEjId,
                    'numero' => $urg->numero,
                    'nombre' => $urg->nombre,
                    'cerrada' => $urg->cerrada
                ]);
                $urgMap[$urg->unidad_responsable_gasto_id] = $newUrgId;
            }

            // Copy 'responsables_operativos'
            $ros = DB::table('responsables_operativos')->whereIn('unidad_responsable_gasto_id', array_keys($urgMap))->get();
            $roMap = [];
            foreach ($ros as $ro) {
                $newRoId = DB::table('responsables_operativos')->insertGetId([
                    'unidad_responsable_gasto_id' => $urgMap[$ro->unidad_responsable_gasto_id],
                    'numero' => $ro->numero,
                    'nombre' => $ro->nombre
                ]);
                $roMap[$ro->responsable_operativo_id] = $newRoId;
            }

            // Copy 'programas'
            $programas = DB::table('programas')->where('ejercicio_id', $ejercicioId)->get();
            $progMap = [];
            foreach ($programas as $p) {
                $newProgId = DB::table('programas')->insertGetId([
                    'ejercicio_id' => $newEjId,
                    'numero' => $p->numero,
                    'nombre' => $p->nombre
                ]);
                $progMap[$p->programa_id] = $newProgId;
            }

            // Copy 'subprogramas'
            $subprogramas = DB::table('subprogramas')->whereIn('programa_id', array_keys($progMap))->get();
            $subProgMap = [];
            foreach ($subprogramas as $sp) {
                $newSpId = DB::table('subprogramas')->insertGetId([
                    'programa_id' => $progMap[$sp->programa_id],
                    'numero' => $sp->numero,
                    'nombre' => $sp->nombre
                ]);
                $subProgMap[$sp->subprograma_id] = $newSpId;
            }

            // Unidades de medida (if they are bound to ejercicio)
            // The old DB seems to have 'unidades_medidas' with 'ejercicio_id'
            $unidadesMedida = DB::table('unidades_medidas')->where('ejercicio_id', $ejercicioId)->get();
            foreach ($unidadesMedida as $um) {
                DB::table('unidades_medidas')->insertGetId([
                    'ejercicio_id' => $newEjId,
                    'numero' => $um->numero,
                    'nombre' => $um->nombre,
                    'descripcion' => $um->descripcion
                ]);
            }

            // Proyectos, Metas, etc could be copied similarly if requested,
            // but the old system truncated them for the new year in the Anteproyecto DB
            // We just set up the catalogues.

            DB::commit();
            return response()->json(['message' => 'Anteproyecto generado y catálogos copiados exitosamente para el año ' . $nuevoAnio]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al generar anteproyecto', 'error' => $e->getMessage()], 500);
        }
    }
}
