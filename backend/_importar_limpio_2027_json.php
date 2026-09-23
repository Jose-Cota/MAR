<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

DB::beginTransaction();
try {
    // Delete only 2027 projects and related records
    $oldProjects = DB::table('proyectos')->where('ejercicio_id', 19)->pluck('proyecto_id')->toArray();
    if (!empty($oldProjects)) {
        $oldActions = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $oldProjects)->pluck('accion_sustantiva_id')->toArray();
        if (!empty($oldActions)) {
            DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $oldActions)->delete();
            DB::table('acciones_sustantivas')->whereIn('proyecto_id', $oldProjects)->delete();
        }
        DB::table('proyectos')->where('ejercicio_id', 19)->delete();
    }

    $rosEjercicio = DB::table('responsables_operativos')->where('ejercicio_id', 19)->get();
    $roMap = [];
    
    // Exact overrides to fix known mapping issues
    $exactMap = [
        'Secretaría Administrativa' => 446,
        'Dirección de Recursos Humanos' => 448,
        'Dirección de Recursos Materiales y Servicios Generales' => 449,
        'Dirección General Jurídica' => 453,
        'Coordinación de Comunicación Social y Relaciones Públicas' => 468,
        'Coordinación de Archivo' => 463,
    ];
    
    foreach ($seed['areas'] as $area) {
        $areaName = trim($area['name']);
        
        if (isset($exactMap[$areaName])) {
            $roMap[$area['id']] = $exactMap[$areaName];
            continue;
        }
        
        $urgNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower($areaName));
        $urgNombreClean = preg_replace('/[éèëê]/u', 'e', $urgNombreClean);
        $urgNombreClean = preg_replace('/[íìïî]/u', 'i', $urgNombreClean);
        $urgNombreClean = preg_replace('/[óòöô]/u', 'o', $urgNombreClean);
        $urgNombreClean = preg_replace('/[úùüû]/u', 'u', $urgNombreClean);
        
        $palabras = array_filter(
            explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $urgNombreClean)),
            fn($p) => mb_strlen($p) > 5
        );
        
        $matchedRo = null;
        $maxCoincidencias = 0;
        foreach ($rosEjercicio as $ro) {
            $roNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($ro->nombre)));
            $roNombreClean = preg_replace('/[éèëê]/u', 'e', $roNombreClean);
            $roNombreClean = preg_replace('/[íìïî]/u', 'i', $roNombreClean);
            $roNombreClean = preg_replace('/[óòöô]/u', 'o', $roNombreClean);
            $roNombreClean = preg_replace('/[úùüû]/u', 'u', $roNombreClean);
            
            $roNombreClean = str_replace(['director', 'directora'], 'direccion', $roNombreClean);
            $roNombreClean = str_replace(['presidente', 'presidenta'], 'presidencia', $roNombreClean);
            $roNombreClean = str_replace(['secretario', 'secretaria'], 'secretaria', $roNombreClean);
            $roNombreClean = str_replace(['contralor', 'contralora'], 'contraloria', $roNombreClean);
            $roNombreClean = str_replace(['interno', 'interna'], 'interna', $roNombreClean);
            $roNombreClean = str_replace(['defensor', 'defensora'], 'defensoria', $roNombreClean);
            $roNombreClean = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $roNombreClean);
            
            $coincidencias = 0;
            foreach ($palabras as $palabra) {
                if (strpos($roNombreClean, $palabra) !== false) {
                    $coincidencias++;
                }
            }
            if ($coincidencias > $maxCoincidencias) {
                $maxCoincidencias = $coincidencias;
                $matchedRo = $ro->responsable_operativo_id;
            }
        }
        
        if ($matchedRo) {
            $roMap[$area['id']] = $matchedRo;
        } else {
            echo "COULD NOT MATCH: {$area['name']}\n";
        }
    }

    $jsonToDbProject = [];
    $jsonToDbAction = [];
    
    $pCount = 0;
    foreach ($seed['poaProjects'] ?? [] as $p) {
        if ($p['exercise'] == 2027) {
            $roId = $roMap[$p['areaId']] ?? 438; // Fallback
            
            $pid = DB::table('proyectos')->insertGetId([
                'nombre' => $p['name'],
                'ejercicio_id' => 19,
                'subprograma_id' => 1,
                'numero' => '01',
                'tipo' => 'normal',
                'fecha' => now()->toDateString(),
                'version' => 1,
                'objetivo' => $r['objective'] ?? '',
                'justificacion' => '',
                'descripcion' => $p['description'] ?? '',
                'nombre_responsable_operativo' => '',
                'cargo_responsable_operativo' => '',
                'nombre_titular' => '',
                'responsable_ficha' => '',
                'autorizado_por' => '',
                'responsable_operativo_id' => $roId
            ]);
            $jsonToDbProject[$p['id']] = $pid;
            $pCount++;
        }
    }
    
    $aCount = 0;
    foreach ($seed['poaActions'] ?? [] as $a) {
        $newPid = $jsonToDbProject[$a['projectId']] ?? null;
        if ($newPid && $a['exercise'] == 2027) {
            $aid = DB::table('acciones_sustantivas')->insertGetId([
                'descripcion' => $a['text'],
                'proyecto_id' => $newPid,
                'numero' => $a['number'] ?? '01'
            ]);
            $jsonToDbAction[$a['id']] = $aid;
            $aCount++;
        }
    }
    
    $lCount = 0;
    $inserts = [];
    foreach ($seed['risks'] ?? [] as $r) {
        if (strpos($r['id'], '2027') !== false && !empty($r['linkedActionIds'])) {
            $dbRisk = DB::table('riesgos')->where('local_id', $r['localId'])->where('ejercicio_id', 19)->first();
            
            if (!$dbRisk) {
                // Find area by mapping the action's project back to its RO and then to the area
                $areaId = $roMap[explode('-', $r['id'])[0]] ?? null;
                if (!$areaId) {
                    $firstActionId = $r['linkedActionIds'][0];
                    $action = collect($seed['poaActions'])->firstWhere('id', $firstActionId);
                    if ($action) {
                        $areaId = $roMap[$action['areaId']] ?? null;
                    }
                }
                
                $rid = DB::table('riesgos')->insertGetId([
                    'ejercicio_id' => 19,
                    'area_id' => $areaId ?? 1,
                    'local_id' => $r['localId'],
                    'riesgo' => $r['risk'] ?? '',
                    'objetivo' => $r['objective'] ?? '',
                    'factores' => $r['factors'] ?? '',
                    'probabilidad' => 0,
                    'impacto' => 0,
                    'probabilidad_inicial' => 0,
                    'impacto_inicial' => 0,
                    'status' => 'Captura',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $dbRisk = DB::table('riesgos')->find($rid);
            }
            
            if ($dbRisk) {
                foreach ($r['linkedActionIds'] as $linkedId) {
                    $newAid = $jsonToDbAction[$linkedId] ?? null;
                    if ($newAid) {
                        $inserts[] = [
                            'actividad_sustantiva_id' => $newAid,
                            'riesgo_id' => $dbRisk->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        $lCount++;
                    }
                }
            }
        }
    }
    if (!empty($inserts)) {
        DB::table('actividad_riesgo')->insert($inserts);
    }

    DB::commit();
    echo "ALL DONE SUCCESSFULLY! Mapped $pCount projects and $aCount actions.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
