<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracionTrimestreController extends Controller
{
    public function getByUR(Request $request, $ur_id, $ejercicio_id)
    {
        $config = \App\Models\ConfiguracionTrimestre::firstOrCreate(
            ['unidad_responsable_id' => $ur_id, 'ejercicio_id' => $ejercicio_id],
            ['t1_abierto' => false, 't2_abierto' => false, 't3_abierto' => false, 't4_abierto' => false]
        );
        return response()->json($config);
    }

    public function update(Request $request, $ur_id, $ejercicio_id)
    {
        $data = $request->only(['t1_abierto', 't2_abierto', 't3_abierto', 't4_abierto']);
        
        if ($ur_id === 'todas') {
            $unidades = \Illuminate\Support\Facades\DB::connection('poa_prod')
                ->table('unidades_responsables_gastos')
                ->where('unidad_responsable_gasto_id', '<', 240)
                ->pluck('unidad_responsable_gasto_id');
                
            $upsertData = [];
            $now = now();
            foreach ($unidades as $u) {
                $upsertData[] = [
                    'unidad_responsable_id' => $u,
                    'ejercicio_id' => $ejercicio_id,
                    't1_abierto' => $data['t1_abierto'] ?? false,
                    't2_abierto' => $data['t2_abierto'] ?? false,
                    't3_abierto' => $data['t3_abierto'] ?? false,
                    't4_abierto' => $data['t4_abierto'] ?? false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            
            \App\Models\ConfiguracionTrimestre::upsert(
                $upsertData,
                ['unidad_responsable_id', 'ejercicio_id'],
                ['t1_abierto', 't2_abierto', 't3_abierto', 't4_abierto', 'updated_at']
            );
            
            return response()->json(['message' => 'Actualizado en todas']);
        } else {
            $config = \App\Models\ConfiguracionTrimestre::firstOrCreate(
                ['unidad_responsable_id' => $ur_id, 'ejercicio_id' => $ejercicio_id],
                ['t1_abierto' => false, 't2_abierto' => false, 't3_abierto' => false, 't4_abierto' => false]
            );
            $config->update($data);
            return response()->json($config);
        }
    }
}
