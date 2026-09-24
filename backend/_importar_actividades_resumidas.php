<?php
/**
 * _importar_actividades_resumidas.php
 * 
 * Opción A: Reemplaza acciones_sustantivas (2026 y 2027) con las actividades
 * resumidas del JSON, y reconstruye actividad_riesgo.
 * Los riesgos NO se modifican.
 */
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// ─── 1. Cargar el JSON ────────────────────────────────────────────────────────
$jsonPath = __DIR__ . '/../Respaldo_MAR_TECDMX_2026-09-23.json';
if (!file_exists($jsonPath)) {
    die("❌ No se encontró el archivo JSON: $jsonPath\n");
}
$json = json_decode(file_get_contents($jsonPath), true);
$poaActions = $json['poaActions'] ?? [];
$risks      = $json['risks'] ?? [];
echo "✅ JSON cargado: " . count($poaActions) . " actividades, " . count($risks) . " riesgos\n\n";

// ─── 2. Mapeo JSON areaId → DB area_id (numérico) ────────────────────────────
// Lo construimos dinámicamente desde la tabla areas comparando nombres
$dbAreas    = DB::table('areas')->get();
$dbProyectos2026 = DB::table('proyectos')->where('ejercicio_id', 17)->get();
$dbProyectos2027 = DB::table('proyectos')->where('ejercicio_id', 19)->get();

// Mapeo manual basado en los nombres del JSON vs BD
// JSON areaId → {area_id_db, proyecto_id_2026, proyecto_id_2027}
$areaMap = [
    'PRES'    => 'Presidencia',
    'SG'      => 'Secretaría General',
    'SA'      => 'Secretaría Administrativa',
    'DPyRF'   => 'Dirección de Planeación y Recursos Financieros',
    'DRH'     => 'Dirección de Recursos Humanos',
    'DRMySG'  => 'Dirección de Recursos Materiales y Servicios Generales',
    'CI'      => 'Contraloría Interna',
    'DGJ'     => 'Dirección General Jurídica',
    'CCSyRP'  => 'Comunicación Social',
    'CTyDP'   => 'Transparencia',
    'IFyC'    => 'Instituto de Formación',
    'CCLA'    => 'Controversias Laborales',
    'USI'     => 'Servicios Informáticos',
    'UEyJ'    => 'Estadística',
    'CDyP'    => 'Difusión',
    'CA'      => 'Archivo',
    'CDHyG'   => 'Derechos Humanos',
    'DPPCyPD' => 'Defensoría',
    'CVyRI'   => 'Vinculación',
    'UEPS'    => 'Procedimientos Sancionadores',
    'PAAH'    => 'Ponencia del Magistrado Armando',
    'PJHR'    => 'Ponencia del Magistrado José',
    'POVR'    => 'Ponencia del Magistrado Osiris',
    'PKSL'    => 'Ponencia de la Magistrada Karina',
    'PLPJC'   => 'Ponencia de la Magistrada Laura',
];

// Construir mapeo real: jsonAreaId → db_area_id
$jsonToDbAreaId = [];
$jsonToProyectoId = []; // [jsonAreaId][year] => proyecto_id

foreach ($dbAreas as $dbArea) {
    foreach ($areaMap as $jsonId => $keyword) {
        if (stripos($dbArea->nombre, $keyword) !== false) {
            if (!isset($jsonToDbAreaId[$jsonId])) {
                $jsonToDbAreaId[$jsonId] = $dbArea->area_id;
            }
        }
    }
}

// Mapear proyectos por nombre (2026)
foreach ($dbProyectos2026 as $p) {
    foreach ($areaMap as $jsonId => $keyword) {
        if (stripos($p->nombre, $keyword) !== false && !isset($jsonToProyectoId[$jsonId][2026])) {
            $jsonToProyectoId[$jsonId][2026] = $p->proyecto_id;
        }
    }
}
// Mapear proyectos por nombre (2027)
foreach ($dbProyectos2027 as $p) {
    foreach ($areaMap as $jsonId => $keyword) {
        if (stripos($p->nombre, $keyword) !== false && !isset($jsonToProyectoId[$jsonId][2027])) {
            $jsonToProyectoId[$jsonId][2027] = $p->proyecto_id;
        }
    }
}

echo "=== Mapeo de áreas ===\n";
foreach ($areaMap as $jsonId => $kw) {
    $dbId   = $jsonToDbAreaId[$jsonId] ?? '?';
    $py2026 = $jsonToProyectoId[$jsonId][2026] ?? '?';
    $py2027 = $jsonToProyectoId[$jsonId][2027] ?? '?';
    echo "  $jsonId → area_id=$dbId  proyecto2026=$py2026  proyecto2027=$py2027\n";
}

// ─── 3. Agrupar actividades JSON por área y año ───────────────────────────────
$actionsByAreaYear = [];
foreach ($poaActions as $act) {
    $actionsByAreaYear[$act['areaId']][$act['exercise']][] = $act;
}

// ─── 4. Agrupar riesgos JSON por área y año, indexados por localId ────────────
$risksByAreaYear = [];
foreach ($risks as $r) {
    $risksByAreaYear[$r['areaId']][$r['exercise']][$r['localId']] = $r;
}

// ─── 5. Obtener riesgos de BD indexados por area_id + ejercicio_id + local_id ─
$dbRiesgos = DB::table('riesgos')
    ->whereIn('ejercicio_id', [17, 19])
    ->select('id', 'local_id', 'area_id', 'ejercicio_id')
    ->get();

// Índice: [area_id_db][ejercicio_id][local_id] => id
$dbRiesgoIndex = [];
foreach ($dbRiesgos as $r) {
    $dbRiesgoIndex[$r->area_id][$r->ejercicio_id][$r->local_id] = $r->id;
}

// ─── 6. Procesar cada área/año ────────────────────────────────────────────────
$totalAccionesInsertadas = 0;
$totalVinculosInsertados = 0;
$totalAccionesBorradas   = 0;
$totalVinculosBorrados   = 0;

DB::beginTransaction();
try {
    foreach ($actionsByAreaYear as $jsonAreaId => $years) {
        $dbAreaId = $jsonToDbAreaId[$jsonAreaId] ?? null;

        foreach ($years as $year => $actions) {
            $ejId      = ($year === 2026) ? 17 : 19;
            $proyId    = $jsonToProyectoId[$jsonAreaId][$year] ?? null;

            echo "\n─── $jsonAreaId ($year) → area_id=$dbAreaId  proyecto_id=$proyId ───\n";

            if (!$proyId) {
                echo "  ⚠️  Sin proyecto en BD para este año. Saltando...\n";
                continue;
            }
            if (!$dbAreaId) {
                echo "  ⚠️  Sin area_id en BD. Saltando...\n";
                continue;
            }

            // 6a. Borrar actividad_riesgo vinculados a acciones de este proyecto
            $oldActionIds = DB::table('acciones_sustantivas')
                ->where('proyecto_id', $proyId)
                ->pluck('accion_sustantiva_id')
                ->toArray();

            if (!empty($oldActionIds)) {
                $deleted = DB::table('actividad_riesgo')
                    ->whereIn('actividad_sustantiva_id', $oldActionIds)
                    ->delete();
                $totalVinculosBorrados += $deleted;
                echo "  🗑  actividad_riesgo borrados: $deleted\n";
            }

            // 6b. Borrar acciones_sustantivas del proyecto
            $borradas = DB::table('acciones_sustantivas')
                ->where('proyecto_id', $proyId)
                ->delete();
            $totalAccionesBorradas += $borradas;
            echo "  🗑  acciones_sustantivas borradas: $borradas\n";

            // 6c. Insertar nuevas actividades resumidas
            $newActionIdMap = []; // jsonActionId => new accion_sustantiva_id
            foreach ($actions as $act) {
                $newId = DB::table('acciones_sustantivas')->insertGetId([
                    'proyecto_id'       => $proyId,
                    'numero'            => $act['number'],
                    'descripcion'       => $act['text'],
                    'recursos_asociados'=> null,
                ]);
                $newActionIdMap[$act['id']] = $newId;
                $totalAccionesInsertadas++;
            }
            echo "  ✅ acciones_sustantivas insertadas: " . count($newActionIdMap) . "\n";

            // 6d. Reconstruir actividad_riesgo usando linkedActionIds del JSON
            $jsonRisksForArea = $risksByAreaYear[$jsonAreaId][$year] ?? [];
            foreach ($jsonRisksForArea as $localId => $jsonRisk) {
                $dbRiesgoId = $dbRiesgoIndex[$dbAreaId][$ejId][$localId] ?? null;
                if (!$dbRiesgoId) {
                    echo "  ⚠️  Riesgo $localId no encontrado en BD para area_id=$dbAreaId ej=$ejId\n";
                    continue;
                }

                $linkedIds = $jsonRisk['linkedActionIds'] ?? [];
                foreach ($linkedIds as $jsonActId) {
                    $newActId = $newActionIdMap[$jsonActId] ?? null;
                    if (!$newActId) {
                        echo "  ⚠️  Actividad JSON $jsonActId no encontrada en mapa\n";
                        continue;
                    }
                    DB::table('actividad_riesgo')->insert([
                        'actividad_sustantiva_id' => $newActId,
                        'riesgo_id'               => $dbRiesgoId,
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ]);
                    $totalVinculosInsertados++;
                }
            }
            echo "  ✅ actividad_riesgo insertados: vínculos para área/año\n";
        }
    }

    DB::commit();
    echo "\n\n════════════════════════════════════════\n";
    echo "✅ IMPORTACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "════════════════════════════════════════\n";
    echo "  acciones_sustantivas borradas:   $totalAccionesBorradas\n";
    echo "  acciones_sustantivas insertadas: $totalAccionesInsertadas\n";
    echo "  actividad_riesgo borrados:       $totalVinculosBorrados\n";
    echo "  actividad_riesgo insertados:     $totalVinculosInsertados\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR - Se hizo rollback: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
