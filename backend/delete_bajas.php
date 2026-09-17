<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicio = 2027;

$proyectos = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->where('ejercicios.ejercicio', $ejercicio)
    ->where(function($q) {
        $q->where('proyectos.nombre', 'BAJA')
          ->orWhere('proyectos.status', '0');
    })
    ->select('proyectos.proyecto_id', 'proyectos.nombre')
    ->get();

if ($proyectos->isEmpty()) {
    echo "No BAJA projects found in $ejercicio.\n";
    return;
}

$ids = $proyectos->pluck('proyecto_id')->toArray();
echo "Found " . count($ids) . " BAJA projects in $ejercicio. Deleting...\n";

try {
    DB::connection('poa_prod')->beginTransaction();
    
    // Delete from related tables first
    DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->whereIn('proyecto_id', $ids)->delete();
    DB::connection('poa_prod')->table('actividades_sustantivas')->whereIn('proyecto_id', $ids)->delete();
    
    // Metas
    $metas = DB::connection('poa_prod')->table('metas')->whereIn('proyecto_id', $ids)->get();
    $metaIds = $metas->pluck('meta_id')->toArray();
    if (!empty($metaIds)) {
        DB::connection('poa_prod')->table('metas')->whereIn('meta_id', $metaIds)->delete();
    }
    
    // Delete the projects
    DB::connection('poa_prod')->table('proyectos')->whereIn('proyecto_id', $ids)->delete();
    
    DB::connection('poa_prod')->commit();
    echo "Successfully deleted BAJA projects.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
