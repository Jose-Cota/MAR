<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$areas = DB::table('unidades_responsables_gastos')->orderBy('unidad_responsable_gasto_id')->get();

$out = [];

foreach ($areas as $area) {
    $areaId = $area->unidad_responsable_gasto_id ?? $area->id_unidad;
    $nombre = $area->nombre ?? $area->denominacion;
    
    // Check if this area even has 2027 risks
    $riesgos2027 = DB::table('riesgos')->where('ejercicio_id', 19)->where('area_id', $areaId)->get();
    
    if ($riesgos2027->isEmpty()) continue;
    
    // Skip the ones we already did (10 and 14)
    if (in_array($areaId, [10, 14])) continue;
    
    $areaData = [
        'id' => $areaId,
        'nombre' => $nombre,
        'riesgos2027' => [],
        'proyectos2027' => [],
        'acciones2026' => []
    ];
    
    foreach ($riesgos2027 as $r) {
        $areaData['riesgos2027'][] = ['local_id' => $r->local_id, 'riesgo' => $r->riesgo];
    }
    
    $roIds = DB::table('responsables_operativos')
                ->where('unidad_responsable_gasto_id', $areaId)
                ->pluck('responsable_operativo_id');
                
    if ($roIds->isEmpty()) {
        $roIds = DB::table('responsables_operativos')
                    ->where('unidad_responsable_gasto_id', str_pad($areaId, 2, '0', STR_PAD_LEFT))
                    ->pluck('responsable_operativo_id');
    }
    
    if ($roIds->isNotEmpty()) {
        $proy2027 = DB::table('proyectos')->where('ejercicio_id', 19)->whereIn('responsable_operativo_id', $roIds)->get();
        foreach ($proy2027 as $p) {
            $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            $actsArr = [];
            foreach ($acts as $a) $actsArr[] = $a->descripcion;
            $areaData['proyectos2027'][] = [
                'id' => $p->proyecto_id,
                'nombre' => $p->nombre,
                'acciones_largas' => $actsArr
            ];
        }
        
        $proy2026 = DB::table('proyectos')->where('ejercicio_id', 18)->whereIn('responsable_operativo_id', $roIds)->get();
        foreach ($proy2026 as $p) {
            $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            foreach ($acts as $a) {
                $areaData['acciones2026'][] = $a->descripcion;
            }
        }
    }
    
    $out[] = $areaData;
}

file_put_contents('C:\Cota\MAR\backend\_todas_las_ur.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Generado _todas_las_ur.json con " . count($out) . " áreas a analizar.\n";
