<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mock = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);

$map_mock = [];
foreach($mock['areas'] as $a) {
    $map_mock[$a['id']] = $a['name'];
}

function normalize($str) {
    return strtolower(trim(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú','(a)',' '], ['a','e','i','o','u','a','e','i','o','u','',''], $str)));
}

$ur_to_mock = [];
$urs = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 19)->get();
foreach($urs as $ur) {
    $name = normalize($ur->nombre);
    foreach($map_mock as $mock_id => $mock_name) {
        $mname = normalize($mock_name);
        if (strpos($name, $mname) !== false || strpos($mname, $name) !== false) {
            $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
            break;
        }
        if ($mock_id == 'PAAH' && strpos($name, 'armandoambriz') !== false) $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
        if ($mock_id == 'PJHR' && strpos($name, 'josejesushernandez') !== false) $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
        if ($mock_id == 'POVR' && strpos($name, 'osiris') !== false) $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
        if ($mock_id == 'PKSL' && strpos($name, 'karinasalgado') !== false) $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
        if ($mock_id == 'PLPJC' && strpos($name, 'laurapatriciajimenez') !== false) $ur_to_mock[$ur->unidad_responsable_gasto_id] = $mock_id;
    }
}

$ro_to_mock = [];
$ros = DB::table('responsables_operativos')->get();
foreach($ros as $ro) {
    $name = normalize($ro->nombre);
    foreach($map_mock as $mock_id => $mock_name) {
        $mname = normalize($mock_name);
        if (strpos($name, $mname) !== false || strpos($mname, $name) !== false) {
            $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
            break;
        }
        // Manual RO mappings
        if ($mock_id == 'PRES' && strpos($name, 'presidente') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'PAAH' && strpos($name, 'armandoambriz') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'PJHR' && strpos($name, 'josejesushernandez') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'POVR' && strpos($name, 'osiris') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'PKSL' && strpos($name, 'karinasalgado') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'PLPJC' && strpos($name, 'laurapatriciajimenez') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'SG' && strpos($name, 'secretariogeneral') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'SA' && strpos($name, 'secretarioadministrativo') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'DPyRF' && strpos($name, 'planeacionyrecursosfinancieros') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'DRH' && strpos($name, 'recursoshumanos') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'DRMySG' && strpos($name, 'recursosmateriales') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CI' && strpos($name, 'contralorinterno') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'DGJ' && strpos($name, 'generaljuridico') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CCSyRP' && strpos($name, 'comunicacionsocial') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CTyDP' && strpos($name, 'transparenciaydatos') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'IFyC' && strpos($name, 'institutodeformacion') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CCLA' && strpos($name, 'controversiaslaborales') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'USI' && strpos($name, 'serviciosinformaticos') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'UEyJ' && strpos($name, 'estadisticayjurisprudencia') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CDyP' && strpos($name, 'difusionypublicacion') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CA' && strpos($name, 'dearchivo') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CDHyG' && strpos($name, 'derechoshumanos') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'DPPCyPD' && strpos($name, 'defensorciudadano') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'CVyRI' && strpos($name, 'vinculacionyrelaciones') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
        if ($mock_id == 'UEPS' && strpos($name, 'procedimientossancionadores') !== false) $ro_to_mock[$ro->responsable_operativo_id] = $mock_id;
    }
}

$dry_run = in_array('--dry-run', $argv);
DB::beginTransaction();
$total_acciones = 0;

foreach($mock['areas'] as $mock_area) {
    $mock_id = $mock_area['id'];
    
    $l_ur_id = array_search($mock_id, $ur_to_mock);
    $l_ro_ids = array_keys($ro_to_mock, $mock_id);
    
    if (!$l_ur_id || empty($l_ro_ids)) {
        // echo "Missing UR or RO for {$mock_id}\n";
        continue;
    }
    
    $proyectos = DB::table('proyectos')->whereIn('responsable_operativo_id', $l_ro_ids)->where('ejercicio_id', 19)->get();
    if ($proyectos->count() == 0) continue;
    
    foreach($proyectos as $p) {
        $acciones_viejas = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
        if (!$dry_run) {
            foreach($acciones_viejas as $a) {
                DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $a->accion_sustantiva_id)->delete();
                DB::table('acciones_sustantivas')->where('accion_sustantiva_id', $a->accion_sustantiva_id)->delete();
            }
        }
    }
    
    $mock_actions = [];
    foreach($mock['poaActions'] as $ma) {
        if ($ma['areaId'] == $mock_id && $ma['exercise'] == 2027) $mock_actions[] = $ma;
    }
    usort($mock_actions, function($a, $b) { return $a['number'] <=> $b['number']; });
    
    $mock_project_ids = array_unique(array_column($mock_actions, 'projectId'));
    $project_map = [];
    $p_idx = 0;
    foreach($mock_project_ids as $mp_id) {
        if (isset($proyectos[$p_idx])) $project_map[$mp_id] = $proyectos[$p_idx]->proyecto_id;
        else $project_map[$mp_id] = $proyectos[0]->proyecto_id;
        $p_idx++;
    }
    
    $created_actions = [];
    foreach($mock_actions as $ma) {
        $laravel_p_id = $project_map[$ma['projectId']];
        if (!$dry_run) {
            $id = DB::table('acciones_sustantivas')->insertGetId([
                'proyecto_id' => $laravel_p_id,
                'descripcion' => $ma['text'],
                'numero' => $ma['number'],
                'recursos_asociados' => null
            ]);
            $created_actions[$ma['id']] = $id;
        } else {
            $created_actions[$ma['id']] = 'dummy_' . $ma['id'];
        }
    }
    
    $mock_risks = [];
    foreach($mock['risks'] as $mr) {
        if ($mr['areaId'] == $mock_id && $mr['exercise'] == 2027) $mock_risks[] = $mr;
    }
    
    $laravel_risks = DB::table('riesgos')->where('area_id', $l_ur_id)->where('ejercicio_id', 19)->orderBy('id')->get();
    
    foreach($mock_risks as $mr) {
        preg_match('/\d+/', $mr['localId'] ?? $mr['id'], $m);
        $risk_num = $m[0] ?? 1;
        
        $l_risk = $laravel_risks->firstWhere('local_id', 'R'.$risk_num) ?: $laravel_risks->first();
        if ($l_risk) {
            $linked = $mr['linkedActionIds'] ?? [];
            foreach($linked as $action_mock_id) {
                if (isset($created_actions[$action_mock_id]) && !$dry_run) {
                    DB::table('actividad_riesgo')->insert([
                        'riesgo_id' => $l_risk->id,
                        'actividad_sustantiva_id' => $created_actions[$action_mock_id]
                    ]);
                }
            }
        }
    }
    
    $total_acciones += count($mock_actions);
    echo "MIGRATED: {$mock_area['name']} (" . count($mock_actions) . " actions)\n";
}

if ($dry_run) {
    DB::rollBack();
    echo "\nDRY RUN COMPLETED. Total projected actions: $total_acciones\n";
} else {
    DB::commit();
    echo "\nMIGRATION COMMITTED. Total actions: $total_acciones\n";
}
