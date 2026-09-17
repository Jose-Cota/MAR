<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableroController extends Controller
{
    public function index(Request $request)
    {
        $ejercicio = $request->query('ejercicio');

        // 1. Fetch filtered Unidades Responsables
        $queryUnidades = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos as urg')
            ->select('urg.*')
            ->orderBy('urg.numero');
            
        if ($ejercicio) {
            $queryUnidades->join('ejercicios as e', 'urg.ejercicio_id', '=', 'e.ejercicio_id')
                          ->where('e.ejercicio', $ejercicio);
        }
        
        $unidades = $queryUnidades->get();
        $urIds = $unidades->pluck('unidad_responsable_gasto_id')->toArray();

        // 2. Fetch Responsables Operativos for those URs
        $responsables = [];
        if (!empty($urIds)) {
            $responsables = DB::connection('poa_prod')
                ->table('responsables_operativos')
                ->whereIn('unidad_responsable_gasto_id', $urIds)
                ->orderBy('numero')
                ->get();
        }

        // Group responsables by unidad_responsable_gasto_id
        $responsablesGrouped = [];
        foreach ($responsables as $ro) {
            $responsablesGrouped[$ro->unidad_responsable_gasto_id][] = $ro;
        }

        // Attach to unidades
        foreach ($unidades as $unidad) {
            $unidad->responsables_operativos = $responsablesGrouped[$unidad->unidad_responsable_gasto_id] ?? [];
        }

        return response()->json($unidades);
    }
}
