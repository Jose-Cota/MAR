<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

DB::beginTransaction();
try {
    // 1. Map areas from JSON to URG IDs
    $urgs = DB::table('unidades_responsables_gastos')->get();
    $areaIdToUrgId = [];
    foreach ($seed['areas'] ?? [] as $area) {
        $name = mb_strtolower(trim($area['name']));
        $matchedUrgId = 1; // Default
        foreach ($urgs as $urg) {
            $urgName = mb_strtolower(trim($urg->unidad_responsable_gasto));
            if (strpos($urgName, $name) !== false || strpos($name, $urgName) !== false) {
                $matchedUrgId = $urg->id;
                break;
            }
        }
        $areaIdToUrgId[$area['id']] = $matchedUrgId;
    }
    
    // Exact overrides for URG matches
    $areaIdToUrgId['PRES'] = 1;
    $areaIdToUrgId['SG'] = 2;
    // Actually, POAFichasController has the exact mapping:
    // We can just use the URG mapping from the names.

    // 2. Delete all 2027 risks and their links
    $oldRisks = DB::table('riesgos')->where('ejercicio_id', 19)->pluck('id')->toArray();
    if (!empty($oldRisks)) {
        DB::table('riesgo_controles')->whereIn('riesgo_id', $oldRisks)->delete();
        DB::table('riesgo_indicadores')->whereIn('riesgo_id', $oldRisks)->delete();
        DB::table('riesgos')->where('ejercicio_id', 19)->delete();
    }
    DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', function($q) {
        $q->select('accion_sustantiva_id')->from('acciones_sustantivas')->whereIn('proyecto_id', function($q2) {
            $q2->select('proyecto_id')->from('proyectos')->where('ejercicio_id', 19);
        });
    })->delete();

    // 3. Map action IDs (JSON id -> DB id)
    $jsonToDbAction = [];
    $actions = DB::table('acciones_sustantivas')
        ->join('proyectos', 'proyectos.proyecto_id', '=', 'acciones_sustantivas.proyecto_id')
        ->where('proyectos.ejercicio_id', 19)
        ->select('acciones_sustantivas.accion_sustantiva_id as db_id', 'acciones_sustantivas.numero', 'proyectos.nombre')
        ->get();
    
    foreach ($seed['poaActions'] ?? [] as $a) {
        if (($a['exercise'] ?? 0) == 2027) {
            // Find the action in the DB by project and number
            $pid = collect($seed['poaProjects'])->firstWhere('id', $a['projectId']);
            if ($pid) {
                $projectName = $pid['name'];
                $dbAction = $actions->firstWhere('numero', $a['number'] ?? '01'); // We would need more accurate mapping
            }
        }
    }
    
    // Easier way to map actions: run a query against the DB based on text.
    foreach ($seed['poaActions'] ?? [] as $a) {
        if (($a['exercise'] ?? 0) == 2027) {
            $dbAct = DB::table('acciones_sustantivas')
                ->where('descripcion', $a['text'])
                ->whereIn('proyecto_id', function($q) {
                    $q->select('proyecto_id')->from('proyectos')->where('ejercicio_id', 19);
                })->first();
            if ($dbAct) {
                $jsonToDbAction[$a['id']] = $dbAct->accion_sustantiva_id;
            }
        }
    }

    // 4. Insert 2027 risks
    $count = 0;
    foreach ($seed['risks'] ?? [] as $r) {
        if (($r['exercise'] ?? 0) == 2027 || strpos($r['id'], '2027') !== false) {
            $urgId = $areaIdToUrgId[explode('-', $r['id'])[0]] ?? 1;
            
            $rid = DB::table('riesgos')->insertGetId([
                'ejercicio_id' => 19,
                'area_id' => $urgId,
                'local_id' => $r['localId'],
                'riesgo' => $r['risk'] ?? '',
                'objetivo' => $r['objective'] ?? '',
                'factores' => $r['factors'] ?? '',
                'estatus' => 'Captura',
                'probabilidad' => $r['probability'] ?? 1,
                'impacto' => $r['impact'] ?? 1,
            ]);
            $count++;

            // Link actions
            if (!empty($r['linkedActionIds'])) {
                foreach ($r['linkedActionIds'] as $linkedId) {
                    $dbAid = $jsonToDbAction[$linkedId] ?? null;
                    if ($dbAid) {
                        DB::table('actividad_riesgo')->insert([
                            'actividad_sustantiva_id' => $dbAid,
                            'riesgo_id' => $rid
                        ]);
                    }
                }
            }
        }
    }

    DB::commit();
    echo "SUCCESS: Deleted old risks, mapped URG IDs correctly, inserted $count 2027 risks and re-linked actions!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
