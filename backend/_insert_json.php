<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();

try {
    echo "1. Re-asignando actividades de 2027 a sus proyectos correctos...\n";
    $proyectos_2027 = DB::connection('poa_prod')->table('proyectos')->where('ejercicio_id', 19)->pluck('proyecto_id')->toArray();
    
    if (!empty($proyectos_2027)) {
        DB::connection('poa_prod')->delete("
            DELETE ar FROM actividad_riesgo ar
            JOIN actividades_sustantivas a ON ar.actividad_sustantiva_id = a.id
            WHERE a.proyecto_id IN (" . implode(',', $proyectos_2027) . ")
        ");
        
        $deleted = DB::connection('poa_prod')->table('actividades_sustantivas')
            ->whereIn('proyecto_id', $proyectos_2027)
            ->delete();
            
        echo "   Borradas: $deleted actividades de 2027 para limpiar reasignacion.\n";
    }

    $json = json_decode(file_get_contents(__DIR__ . '/matrix.json'), true);
    
    function getProjectForArea($nombre_area) {
        $query = DB::connection('poa_prod')->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->where('p.ejercicio_id', 19);
            
        if (str_contains($nombre_area, 'Ponencia')) {
            $parts = explode('·', $nombre_area);
            $cleanName = trim($parts[count($parts)-1]); 
            return (clone $query)->where('ro.nombre', $cleanName)
                                 ->where('p.nombre', 'like', '%Ponencia realiza%')
                                 ->first();
        }
        
        if (str_contains($nombre_area, 'Planeación y Recursos Financieros')) {
            return (clone $query)->where('ro.nombre', 'like', '%Planeación y Recursos Financieros%')->first();
        }
        if (str_contains($nombre_area, 'Dirección de Recursos Humanos')) {
            return (clone $query)->where('ro.nombre', 'like', '%Recursos Humanos%')->first();
        }
        if (str_contains($nombre_area, 'Recursos Materiales')) {
            return (clone $query)->where('ro.nombre', 'like', '%Recursos Materiales%')->first();
        }
        if (str_contains($nombre_area, 'Secretaría Administrativa')) {
            // Ensure we don't accidentally get one of the sub-direcciones if we search by urg
            return (clone $query)->where('ro.nombre', 'like', 'Secretario (a)  Administrativo (a)')->first();
        }
        if (str_contains($nombre_area, 'Controversias Laborales')) {
            return (clone $query)->where('urg.nombre', 'like', '%Controversias Laborales%')->first();
        }
        
        $exact = (clone $query)->where('urg.nombre', $nombre_area)->first();
        if ($exact) return $exact;
        
        return (clone $query)->where('urg.nombre', 'like', "%$nombre_area%")->first();
    }

    function getAreaIdForRiesgo($nombre_area) {
        if (str_contains($nombre_area, 'Ponencia')) {
            $parts = explode('·', $nombre_area);
            $cleanName = trim($parts[count($parts)-1]);
            $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 1)->where('nombre', $cleanName)->first();
            if ($urg) return $urg->unidad_responsable_gasto_id;
            
            $urg_struct = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 19)->where('nombre', $cleanName)->first();
            if ($urg_struct) return $urg_struct->unidad_responsable_gasto_id;
            
            return null;
        }

        $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 1)->where('nombre', $nombre_area)->first();
        if ($urg) return $urg->unidad_responsable_gasto_id;
        
        if (str_contains($nombre_area, 'Controversias Laborales')) {
            $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 1)->where('nombre', 'like', '%Controversias Laborales%')->first();
            if ($urg) return $urg->unidad_responsable_gasto_id;
        }
        
        $urg_struct = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 19)->where('nombre', $nombre_area)->first();
        if ($urg_struct) return $urg_struct->unidad_responsable_gasto_id;
        
        return null;
    }

    $inserted = 0;
    foreach ($json as $item) {
        $nombre_area = $item['nombre_area'];
        
        $p = getProjectForArea($nombre_area);
        
        if (!$p) {
            echo "   [WARNING] Proyecto 2027 no encontrado para: $nombre_area\n";
            continue;
        }
        
        $act_id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
            'proyecto_id' => $p->proyecto_id,
            'numero' => $item['no_consecutivo'],
            'descripcion' => $item['actividad_alineacion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
        $inserted++;
        
        $area_id = getAreaIdForRiesgo($nombre_area);
        $urg_struct_id = $p->unidad_responsable_gasto_id;
        
        $riesgos_arr = array_map('trim', explode(',', $item['riesgos_vinculados']));
        foreach ($riesgos_arr as $r_local) {
            $riesgo = DB::connection('poa_prod')->table('riesgos')
                ->where('ejercicio_id', 19)
                ->where(function($q) use ($area_id, $urg_struct_id) {
                    if ($area_id) $q->where('area_id', $area_id);
                    $q->orWhere('area_id', $urg_struct_id);
                })
                ->where('local_id', $r_local)
                ->first();
                
            if (!$riesgo && str_contains($nombre_area, 'Ponencia')) {
                $riesgo = DB::connection('poa_prod')->table('riesgos')
                    ->where('ejercicio_id', 19)
                    ->where('local_id', $r_local)
                    ->whereBetween('area_id', [21, 25])
                    ->first();
            }
                
            if ($riesgo) {
                DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $act_id,
                    'riesgo_id' => $riesgo->id
                ]);
            }
        }
    }

    echo "   Insertadas $inserted actividades de JSON.\n";
    
    // Clonar a Armando Ambriz
    $p_armando = DB::connection('poa_prod')->table('proyectos as p')
        ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->where('p.ejercicio_id', 19)
        ->where('ro.nombre', 'Ponencia del Magistrado Armando Ambriz Hernández')
        ->where('p.nombre', 'like', '%Ponencia realiza%')
        ->select('p.proyecto_id')
        ->first();
        
    if ($p_armando) {
        $actividades_std = [
            ["no_consecutivo" => 1, "actividad_alineacion" => "Garantizar la autonomía del Tribunal, respecto del funcionamiento, independencia e imparcialidad de sus decisiones.", "riesgos_vinculados" => "R5"],
            ["no_consecutivo" => 2, "actividad_alineacion" => "Ejercer la función jurisdiccional aplicando la potestad de emitir resoluciones y determinaciones.", "riesgos_vinculados" => "R1"],
            ["no_consecutivo" => 3, "actividad_alineacion" => "Aprobar los criterios de jurisprudencia y tesis relevantes.", "riesgos_vinculados" => "R3"],
            ["no_consecutivo" => 4, "actividad_alineacion" => "Llevar a cabo Sesiones Públicas para la resolución de los medios de impugnación y controversias planteadas ante este Tribunal.", "riesgos_vinculados" => "R2"],
            ["no_consecutivo" => 5, "actividad_alineacion" => "Participar en la integración de las Comisiones de Magistrados/as, que el Pleno determine y presentar los informes y acuerdos correspondientes.", "riesgos_vinculados" => "R4"]
        ];
        
        foreach ($actividades_std as $item) {
            $act_id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
                'proyecto_id' => $p_armando->proyecto_id,
                'numero' => $item['no_consecutivo'],
                'descripcion' => $item['actividad_alineacion'],
                'recursos_asociados' => '',
                'es_resumida' => 1
            ]);
            $inserted++;
            
            $riesgo = DB::connection('poa_prod')->table('riesgos')->where('ejercicio_id', 19)->where('local_id', $item['riesgos_vinculados'])->whereBetween('area_id', [21, 25])->first();
            if ($riesgo) {
                DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $act_id,
                    'riesgo_id' => $riesgo->id
                ]);
            }
        }
        echo "   Clonadas 5 actividades para Armando Ambriz.\n";
    }

    DB::connection('poa_prod')->commit();
    echo "PROCESO COMPLETADO EXITOSAMENTE.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
