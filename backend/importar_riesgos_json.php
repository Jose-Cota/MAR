<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function import_riesgos() {
    $file = 'C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json';
    if (!file_exists($file)) {
        die("File not found\n");
    }
    
    $data = json_decode(file_get_contents($file), true);
    if (!$data) {
        die("Invalid JSON\n");
    }

    DB::beginTransaction();

    try {
        echo "1. Eliminando riesgos existentes de 2026 y 2027...\n";
        
        $ejercicios = DB::table('ejercicios')->whereIn('ejercicio', [2026, 2027])->pluck('ejercicio_id', 'ejercicio')->toArray();
        if (empty($ejercicios)) {
            // Fallback for missing years
            $ejercicios = [2026 => 18, 2027 => 19];
        }
        
        $riesgosIds = DB::table('riesgos')->whereIn('ejercicio_id', array_values($ejercicios))->pluck('id')->toArray();
        
        if (!empty($riesgosIds)) {
            DB::table('riesgo_controles')->whereIn('riesgo_id', $riesgosIds)->delete();
            DB::table('riesgo_indicadores')->whereIn('riesgo_id', $riesgosIds)->delete();
            DB::table('actividad_riesgo')->whereIn('riesgo_id', $riesgosIds)->delete();
            DB::table('riesgos')->whereIn('id', $riesgosIds)->delete();
        }

        echo "2. Mapeando URGs...\n";
        $urgMap = []; // areaId from JSON -> urg_id in DB
        foreach ($data['areas'] as $area) {
            $urg = DB::table('unidades_responsables_gastos')->where('nombre', $area['name'])->first();
            if ($urg) {
                $urgMap[$area['id']] = $urg->unidad_responsable_gasto_id;
            } else {
                echo "Warning: URG not found for {$area['name']}\n";
            }
        }

        // Action Mapping dictionary (text -> db_id) for 2026
        $actionDescMap2026 = [];
        foreach ($data['poaActions'] ?? [] as $act) {
            $actionDescMap2026[$act['id']] = $act['text'];
        }
        
        // Dictionary for mapping 2027 Activity ID Strings (like POA2027-020603-A1) to DB Actividades Sustantivas ID.
        // It's extremely hard to map perfectly without the original project structure mapping in DB vs JSON. 
        // We will try a heuristic matching by action numbers if available, or just skip if we can't find it reliably.
        // Or if the poaProjects in JSON has descriptions, we could match them.

        echo "3. Insertando Riesgos y dependencias...\n";
        $countR = 0;
        $countAR = 0;
        
        foreach ($data['risks'] as $r) {
            $ej = $r['exercise'];
            $ej_id = $ejercicios[$ej] ?? null;
            $urg_id = $urgMap[$r['areaId']] ?? null;
            
            if (!$ej_id || !$urg_id) {
                continue;
            }
            
            $riesgo_id = DB::table('riesgos')->insertGetId([
                'local_id' => $r['localId'],
                'area_id' => $urg_id,
                'ejercicio_id' => $ej_id,
                'objetivo' => $r['objective'] ?? '',
                'riesgo' => $r['risk'] ?? '',
                'factores' => $r['factors'] ?? '',
                'factores_internos' => $r['factors'] ?? '',
                'probabilidad' => $r['probability'] ?? null,
                'impacto' => $r['impact'] ?? null,
                'probabilidad_inicial' => $r['initialProbability'] ?? null,
                'impacto_inicial' => $r['initialImpact'] ?? null,
                'status' => $r['status'] ?? 'Borrador',
                'last_observation' => $r['lastObservation'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $countR++;
            
            // Controles
            if (!empty($r['controls'])) {
                foreach ($r['controls'] as $c) {
                    DB::table('riesgo_controles')->insert([
                        'riesgo_id' => $riesgo_id,
                        'texto' => $c['text'] ?? '',
                        'estado_validacion' => $c['validationStatus'] ?? '',
                        'evidencia_tipo' => $c['evidence']['type'] ?? '',
                        'evidencia_referencia' => $c['evidence']['reference'] ?? '',
                        'evidencia_responsable' => $c['evidence']['responsible'] ?? '',
                        'evidencia_periodicidad' => $c['evidence']['periodicity'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Indicadores
            if (!empty($r['indicators'])) {
                foreach ($r['indicators'] as $i) {
                    DB::table('riesgo_indicadores')->insert([
                        'riesgo_id' => $riesgo_id,
                        'nombre' => $i['name'] ?? '',
                        'tipo' => $i['type'] ?? '',
                        'periodicidad' => $i['periodicity'] ?? '',
                        'formula' => $i['formula'] ?? '',
                        'unidad' => $i['unit'] ?? '',
                        'sentido' => $i['direction'] ?? '',
                        'numerador' => $i['numeratorLabel'] ?? '',
                        'denominador' => $i['denominatorLabel'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Linking Actions
            if (!empty($r['linkedActionIds'])) {
                foreach ($r['linkedActionIds'] as $linkedId) {
                    $db_act_id = null;
                    if ($ej == 2026) {
                        $text = $actionDescMap2026[$linkedId] ?? null;
                        if ($text) {
                            $textLatin = mb_convert_encoding(substr($text, 0, 50), 'ISO-8859-1', 'UTF-8');
                            try {
                                $actDb = DB::table('acciones_sustantivas')->where('descripcion', 'LIKE', '%' . $textLatin . '%')->first();
                                if ($actDb) {
                                    $db_act_id = $actDb->accion_sustantiva_id;
                                }
                            } catch (\Exception $e) {
                                // Fallback if collation error persists
                            }
                        }
                    } else if ($ej == 2027) {
                        // Extract Activity number, e.g. from POA2027-020603-A1 -> 1
                        if (preg_match('/-A(\d+)$/', $linkedId, $matches)) {
                            $numeroActividad = $matches[1];
                            
                            // Get projects for this area and exercise 2027
                            // We don't know exact project mapping, so we grab any activity with this number for this area
                            // This is a heuristic: join actividades_sustantivas with proyectos, filter by URG and activity number
                            $actDb = DB::table('actividades_sustantivas')
                                ->join('proyectos', 'actividades_sustantivas.proyecto_id', '=', 'proyectos.proyecto_id')
                                ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
                                ->where('responsables_operativos.unidad_responsable_gasto_id', $urg_id)
                                ->where('actividades_sustantivas.numero', $numeroActividad)
                                ->select('actividades_sustantivas.id as actividad_id')
                                ->first();

                            if ($actDb) {
                                $db_act_id = $actDb->actividad_id;
                            }
                        }
                    }
                    
                    if ($db_act_id) {
                        DB::table('actividad_riesgo')->insert([
                            'actividad_sustantiva_id' => $db_act_id,
                            'riesgo_id' => $riesgo_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $countAR++;
                    } else {
                        // Could not find a match
                        // echo "Warning: Could not link action/activity $linkedId for risk {$r['localId']}\n";
                    }
                }
            }
        }
        
        DB::commit();
        echo "Importacion completada: $countR riesgos insertados, $countAR links de actividad.\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "Error: " . $e->getMessage() . "\n";
    }
}

import_riesgos();
