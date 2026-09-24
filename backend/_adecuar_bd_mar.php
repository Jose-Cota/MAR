<?php
/**
 * _adecuar_bd_mar.php
 *
 * Adecua la BD del MAR para ser compatible con el JSON:
 * 1. Actualiza los nombres de las áreas en la tabla `areas`
 * 2. Agrega código identificador (clave_json) en el campo `descripcion`
 * 3. Borra y reinserta acciones_sustantivas + actividad_riesgo
 *    usando el mapeo correcto JSON areaId → DB area_id
 */
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// ══════════════════════════════════════════════════════════════════════════════
// MAPEO DEFINITIVO: JSON areaId → DB area_id (derivado de conteo de riesgos)
// ══════════════════════════════════════════════════════════════════════════════
$AREA_MAP = [
    // JSON areaId => [db_area_id, nombre_nuevo, proyecto_ids_2026, proyecto_ids_2027]
    'PRES'    => ['db_area_id' => 1,  'nombre' => 'Presidencia',
                  'py2026' => [1589], 'py2027' => [1767]],
    'SG'      => ['db_area_id' => 7,  'nombre' => 'Secretaría General',
                  'py2026' => [1590], 'py2027' => [1768, 1769, 1770]],
    'SA'      => ['db_area_id' => 8,  'nombre' => 'Secretaría Administrativa',
                  'py2026' => [1591], 'py2027' => [1771]],
    'DPyRF'   => ['db_area_id' => 4,  'nombre' => 'Dirección de Planeación y Recursos Financieros',
                  'py2026' => [1592], 'py2027' => [1772]],
    'DRH'     => ['db_area_id' => 5,  'nombre' => 'Dirección de Recursos Humanos',
                  'py2026' => [1593], 'py2027' => [1773]],
    'DRMySG'  => ['db_area_id' => 6,  'nombre' => 'Dirección de Recursos Materiales y Servicios Generales',
                  'py2026' => [1594], 'py2027' => [1774]],
    'CI'      => ['db_area_id' => 9,  'nombre' => 'Contraloría Interna',
                  'py2026' => [1595], 'py2027' => [1775, 1776, 1777, 1778, 1779]],
    'DGJ'     => ['db_area_id' => 10, 'nombre' => 'Dirección General Jurídica',
                  'py2026' => [1596], 'py2027' => [1780, 1781, 1782]],
    'CCSyRP'  => ['db_area_id' => 14, 'nombre' => 'Coordinación de Comunicación Social y Relaciones Públicas',
                  'py2026' => [1597], 'py2027' => [1783, 1784]],
    'CTyDP'   => ['db_area_id' => 15, 'nombre' => 'Coordinación de Transparencia y Datos Personales',
                  'py2026' => [1598], 'py2027' => [1803]],
    'IFyC'    => ['db_area_id' => 11, 'nombre' => 'Instituto de Formación y Capacitación',
                  'py2026' => [1599], 'py2027' => [1788]],
    'CCLA'    => ['db_area_id' => 17, 'nombre' => 'Comisión de Controversias Laborales y Administrativas',
                  'py2026' => [1600], 'py2027' => [1789]],
    'USI'     => ['db_area_id' => 13, 'nombre' => 'Unidad de Servicios Informáticos',
                  'py2026' => [1601], 'py2027' => [1790, 1791]],
    'UEyJ'    => ['db_area_id' => 12, 'nombre' => 'Unidad de Estadística y Jurisprudencia',
                  'py2026' => [1602], 'py2027' => [1792, 1793]],
    'CDyP'    => ['db_area_id' => 16, 'nombre' => 'Coordinación de Difusión y Publicación',
                  'py2026' => [1603], 'py2027' => [1794, 1795, 1796]],
    'CA'      => ['db_area_id' => 18, 'nombre' => 'Coordinación de Archivo',
                  'py2026' => [1604], 'py2027' => [1797]],
    'CDHyG'   => ['db_area_id' => 19, 'nombre' => 'Coordinación de Derechos Humanos y Género',
                  'py2026' => [1605], 'py2027' => [1798]],
    'DPPCyPD' => ['db_area_id' => 20, 'nombre' => 'Defensoría Pública de Participación Ciudadana y de Procesos Democráticos',
                  'py2026' => [1606], 'py2027' => [1799]],
    'CVyRI'   => ['db_area_id' => 3,  'nombre' => 'Coordinación de Vinculación y Relaciones Internacionales',
                  'py2026' => [1607], 'py2027' => [1800, 1802]],
    'UEPS'    => ['db_area_id' => 2,  'nombre' => 'Unidad Especializada de Procedimientos Sancionadores',
                  'py2026' => [1608], 'py2027' => [1801]],
    // Ponencias 2026 usan proyectos Alineación técnica (1609-1618)
    // Ponencias 2027 usan sus propios proyectos (1757-1766)
    'PAAH'    => ['db_area_id' => 21, 'nombre' => 'Ponencia del Magistrado Armando Ámbriz Hernández',
                  'py2026' => [1609, 1610], 'py2027' => [1757, 1758]],
    'PJHR'    => ['db_area_id' => 22, 'nombre' => 'Ponencia del Magistrado José Jesús Hernández Rodríguez',
                  'py2026' => [1611, 1612], 'py2027' => [1759, 1760]],
    'POVR'    => ['db_area_id' => 23, 'nombre' => 'Ponencia del Magistrado Osiris Vázquez Rangel',
                  'py2026' => [1613, 1614], 'py2027' => [1761, 1762]],
    'PKSL'    => ['db_area_id' => 24, 'nombre' => 'Ponencia de la Magistrada Karina Salgado Lunar',
                  'py2026' => [1615, 1616], 'py2027' => [1763, 1764]],
    'PLPJC'   => ['db_area_id' => 25, 'nombre' => 'Ponencia de la Magistrada Laura Patricia Jiménez Castillo',
                  'py2026' => [1617, 1618], 'py2027' => [1765, 1766]],
];

// ══════════════════════════════════════════════════════════════════════════════
// PASO 1: Actualizar nombres de áreas en BD
// ══════════════════════════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "PASO 1: Actualizando nombres de áreas\n";
echo "═══════════════════════════════════════════════════════\n";
DB::beginTransaction();
try {
    foreach ($AREA_MAP as $jsonId => $cfg) {
        $aId = $cfg['db_area_id'];
        $current = DB::table('areas')->where('area_id', $aId)->first();
        $newNombre = $cfg['nombre'];
        DB::table('areas')->where('area_id', $aId)->update([
            'nombre'      => $newNombre,
            'descripcion' => $jsonId,  // guardamos el código JSON como referencia
        ]);
        echo "  area_id=$aId: '{$current->nombre}' → '$newNombre' [clave=$jsonId]\n";
    }
    DB::commit();
    echo "  ✅ Nombres actualizados\n\n";
} catch (\Exception $e) {
    DB::rollBack();
    die("  ❌ Error al actualizar áreas: " . $e->getMessage() . "\n");
}

// ══════════════════════════════════════════════════════════════════════════════
// PASO 2: Cargar JSON
// ══════════════════════════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "PASO 2: Cargando JSON\n";
echo "═══════════════════════════════════════════════════════\n";
$json    = json_decode(file_get_contents(__DIR__ . '/../Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$poaActs = $json['poaActions'];
$risks   = $json['risks'];
echo "  ✅ " . count($poaActs) . " actividades, " . count($risks) . " riesgos\n\n";

// Agrupar actividades por areaId + año
$actByAreaYear = [];
foreach ($poaActs as $a) {
    $actByAreaYear[$a['areaId']][$a['exercise']][] = $a;
}

// Agrupar riesgos JSON por areaId + año + localId
$riskByAreaYear = [];
foreach ($risks as $r) {
    $riskByAreaYear[$r['areaId']][$r['exercise']][$r['localId']] = $r;
}

// Índice riesgos BD: [db_area_id][ejercicio_id][local_id] => riesgo_id
$dbRiesgos = DB::table('riesgos')
    ->whereIn('ejercicio_id', [17, 19])
    ->select('id', 'local_id', 'area_id', 'ejercicio_id')
    ->get();
$dbRiesgoIdx = [];
foreach ($dbRiesgos as $r) {
    $dbRiesgoIdx[$r->area_id][$r->ejercicio_id][$r->local_id] = $r->id;
}

// ══════════════════════════════════════════════════════════════════════════════
// PASO 3: Reemplazar acciones_sustantivas + reconstruir actividad_riesgo
// ══════════════════════════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "PASO 3: Importando actividades y reconstruyendo vínculos\n";
echo "═══════════════════════════════════════════════════════\n";

$totActBorradas = $totActInsertadas = $totVinBorrados = $totVinInsertados = 0;
$totRiesgosSinMatch = 0;

DB::beginTransaction();
try {
    foreach ($AREA_MAP as $jsonId => $cfg) {
        $dbAreaId = $cfg['db_area_id'];

        foreach ([2026 => 17, 2027 => 19] as $year => $ejId) {
            $proyIds = $cfg["py{$year}"] ?? [];
            $jsonActs = $actByAreaYear[$jsonId][$year] ?? [];

            if (empty($proyIds)) {
                echo "  ⚠️  $jsonId ($year): sin proyecto configurado\n";
                continue;
            }
            if (empty($jsonActs)) {
                echo "  ⚠️  $jsonId ($year): sin actividades en JSON\n";
                continue;
            }

            echo "\n  ── $jsonId ($year) area_id=$dbAreaId | proyectos=" . implode(',', $proyIds) . " ──\n";

            // Borrar vínculos y actividades de TODOS los proyectos de esta área/año
            $oldActIds = DB::table('acciones_sustantivas')
                ->whereIn('proyecto_id', $proyIds)
                ->pluck('accion_sustantiva_id')
                ->toArray();

            if (!empty($oldActIds)) {
                $vb = DB::table('actividad_riesgo')
                    ->whereIn('actividad_sustantiva_id', $oldActIds)->delete();
                $totVinBorrados += $vb;
            }

            $ab = DB::table('acciones_sustantivas')
                ->whereIn('proyecto_id', $proyIds)->delete();
            $totActBorradas += $ab;

            // Insertar actividades resumidas — todas en el PRIMER proyecto de la lista
            $proyPrincipal = $proyIds[0];
            $newActMap = []; // jsonActId => new accion_sustantiva_id
            foreach ($jsonActs as $act) {
                $newId = DB::table('acciones_sustantivas')->insertGetId([
                    'proyecto_id'        => $proyPrincipal,
                    'numero'             => $act['number'],
                    'descripcion'        => $act['text'],
                    'recursos_asociados' => null,
                ]);
                $newActMap[$act['id']] = $newId;
                $totActInsertadas++;
            }
            echo "     ✅ Act. borradas=$ab | insertadas=" . count($newActMap) . "\n";

            // Reconstruir actividad_riesgo
            $jsonRisks = $riskByAreaYear[$jsonId][$year] ?? [];
            $vinIns = 0;
            foreach ($jsonRisks as $localId => $jRisk) {
                $dbRiesgoId = $dbRiesgoIdx[$dbAreaId][$ejId][$localId] ?? null;
                if (!$dbRiesgoId) {
                    echo "     ⚠️  Riesgo $localId no encontrado (area_id=$dbAreaId ej=$ejId)\n";
                    $totRiesgosSinMatch++;
                    continue;
                }
                foreach ($jRisk['linkedActionIds'] as $jActId) {
                    $newActId = $newActMap[$jActId] ?? null;
                    if (!$newActId) {
                        echo "     ⚠️  Actividad JSON $jActId no mapeada\n";
                        continue;
                    }
                    DB::table('actividad_riesgo')->insert([
                        'actividad_sustantiva_id' => $newActId,
                        'riesgo_id'               => $dbRiesgoId,
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ]);
                    $vinIns++;
                    $totVinInsertados++;
                }
            }
            echo "     ✅ Vínculos borrados=" . (isset($vb) ? $vb : 0) . " | insertados=$vinIns\n";
        }
    }

    DB::commit();

    echo "\n\n════════════════════════════════════════════════════\n";
    echo "✅ IMPORTACIÓN COMPLETADA\n";
    echo "════════════════════════════════════════════════════\n";
    echo "  acciones_sustantivas borradas:   $totActBorradas\n";
    echo "  acciones_sustantivas insertadas: $totActInsertadas\n";
    echo "  actividad_riesgo borrados:       $totVinBorrados\n";
    echo "  actividad_riesgo insertados:     $totVinInsertados\n";
    if ($totRiesgosSinMatch > 0) {
        echo "  ⚠️  Riesgos sin match en BD:     $totRiesgosSinMatch\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR - Rollback aplicado: " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}
