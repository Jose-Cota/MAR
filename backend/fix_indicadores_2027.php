<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// We need to find indicators in 2027 that are currently attached to a principal meta.
$indicadores = DB::connection('poa_prod')
    ->table('indicadores as i')
    ->join('metas as m', 'i.meta_id', '=', 'm.meta_id')
    ->join('proyectos as p', 'i.proyecto_id', '=', 'p.proyecto_id')
    ->join('responsables_operativos as r', 'p.responsable_operativo_id', '=', 'r.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'r.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('urg.ejercicio_id', 19) // 19 is 2027
    ->where('m.tipo', 'principal')
    ->select('i.*')
    ->get();

$fixed = 0;
foreach ($indicadores as $ind) {
    // Find complementarias for this project
    $complementarias = DB::connection('poa_prod')
        ->table('metas')
        ->where('proyecto_id', $ind->proyecto_id)
        ->where('tipo', 'complementaria')
        ->get();
    
    // Find which complementarias already have indicators
    $existingInds = DB::connection('poa_prod')
        ->table('indicadores')
        ->where('proyecto_id', $ind->proyecto_id)
        ->pluck('meta_id')
        ->toArray();
        
    $orphan = $complementarias->first(function($m) use ($existingInds) {
        return !in_array($m->meta_id, $existingInds);
    });
    
    if ($orphan) {
        DB::connection('poa_prod')
            ->table('indicadores')
            ->where('indicador_id', $ind->indicador_id)
            ->update([
                'meta_id' => $orphan->meta_id,
                'requiere_revision' => 1
            ]);
        $fixed++;
    }
}

echo "Fixed $fixed indicators.\n";
