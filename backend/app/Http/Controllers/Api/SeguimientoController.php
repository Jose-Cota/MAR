<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguimientoController extends Controller
{
    /**
     * Get metas for a project along with their progress (avances)
     */
    public function getMetasProyecto($proyecto_id)
    {
        // Obtener las metas principales y complementarias del proyecto
        $metas = DB::connection('poa_prod')
            ->table('metas')
            ->where('proyecto_id', $proyecto_id)
            ->get();

        // Para cada meta, obtener sus meses programados y de alcance
        foreach ($metas as $meta) {
            $programados = DB::connection('poa_prod')
                ->table('meses_metas_programadas')
                ->where('meta_id', $meta->meta_id)
                ->orderBy('mes_id')
                ->get();

            $avances = DB::connection('poa_prod')
                ->table('meses_metas_alcanzadas')
                ->where('meta_id', $meta->meta_id)
                ->orderBy('mes_id')
                ->get();
            
            $meta->programados = $programados;
            $meta->avances = $avances;
        }

        return response()->json($metas);
    }

    /**
     * Save the monthly progress for a meta
     */
    public function putAvanceMensual(Request $request)
    {
        $request->validate([
            'meta_id' => 'required|integer',
            'proyecto_id' => 'required|integer',
            'mes_id' => 'required|integer',
            'numero' => 'required|numeric', // Valor capturado
            'explicacion' => 'nullable|string'
        ]);

        $metaId = $request->input('meta_id');
        $proyectoId = $request->input('proyecto_id');
        $mesId = $request->input('mes_id');
        $numero = $request->input('numero');
        $explicacion = $request->input('explicacion', '');

        // Obtener la meta programada para este mes en la tabla meses_metas_programadas
        $programado = DB::connection('poa_prod')
            ->table('meses_metas_programadas')
            ->where('meta_id', $metaId)
            ->where('mes_id', $mesId)
            ->first();

        // Evitar división por cero
        $numeroProgramado = $programado && $programado->numero > 0 ? $programado->numero : 1;
        
        $apa = 0; // Avance porcentaje de meta
        if ($numero > 0) {
            $apa = ($numero * 100) / $numeroProgramado;
        }

        if ($programado && $programado->numero < $numero) {
            return response()->json([
                'message' => 'El número ingresado no puede ser mayor al programado (' . $programado->numero . ')'
            ], 422);
        }

        // Realizar Upsert o Update
        $exists = DB::connection('poa_prod')
            ->table('meses_metas_alcanzadas')
            ->where('meta_id', $metaId)
            ->where('mes_id', $mesId)
            ->exists();

        $data = [
            'numero' => $numero,
            'explicacion' => $explicacion,
            'porcentaje' => round($apa, 2),
            'porcentaje_real' => round($apa, 2) 
        ];

        if ($exists) {
            DB::connection('poa_prod')
                ->table('meses_metas_alcanzadas')
                ->where('meta_id', $metaId)
                ->where('mes_id', $mesId)
                ->update($data);
        } else {
            $data['meta_id'] = $metaId;
            $data['mes_id'] = $mesId;
            DB::connection('poa_prod')
                ->table('meses_metas_alcanzadas')
                ->insert($data);
        }

        return response()->json(['success' => true, 'message' => 'Avance guardado exitosamente.']);
    }
}
