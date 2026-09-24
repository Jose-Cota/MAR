<?php
/**
 * _comparar_areas.php
 * Compara riesgos del JSON vs BD para encontrar el mapeo correcto
 */
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// ── Cargar JSON ──────────────────────────────────────────────────────────────
$json  = json_decode(file_get_contents(__DIR__ . '/../Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$risks = $json['risks'];
$acts  = $json['poaActions'];

// Contar riesgos JSON por areaId + año
$jsonRiskCount = [];
foreach ($risks as $r) {
    $jsonRiskCount[$r['areaId']][$r['exercise']] = ($jsonRiskCount[$r['areaId']][$r['exercise']] ?? 0) + 1;
}
// Contar actividades JSON por areaId + año
$jsonActCount = [];
foreach ($acts as $a) {
    $jsonActCount[$a['areaId']][$a['exercise']] = ($jsonActCount[$a['areaId']][$a['exercise']] ?? 0) + 1;
}

echo "=== JSON: riesgos y actividades por área ===\n";
$jsonAreas = array_keys($jsonRiskCount);
sort($jsonAreas);
foreach ($jsonAreas as $jArea) {
    $r26 = $jsonRiskCount[$jArea][2026] ?? 0;
    $r27 = $jsonRiskCount[$jArea][2027] ?? 0;
    $a26 = $jsonActCount[$jArea][2026]  ?? 0;
    $a27 = $jsonActCount[$jArea][2027]  ?? 0;
    echo "  JSON[$jArea] r2026=$r26 r2027=$r27 | act2026=$a26 act2027=$a27\n";
}

// ── Contar riesgos BD por area_id + ejercicio_id ──────────────────────────────
echo "\n=== BD: riesgos por area_id (ej=17=2026, ej=19=2027) ===\n";
$dbRiesgos = DB::table('riesgos')
    ->whereIn('ejercicio_id', [17, 19])
    ->select('area_id', 'ejercicio_id')
    ->get();

$dbCount = [];
foreach ($dbRiesgos as $r) {
    $dbCount[$r->area_id][$r->ejercicio_id] = ($dbCount[$r->area_id][$r->ejercicio_id] ?? 0) + 1;
}
ksort($dbCount);

$dbAreas = DB::table('areas')->pluck('nombre', 'area_id')->toArray();

foreach ($dbCount as $aId => $byEj) {
    $r17 = $byEj[17] ?? 0;
    $r19 = $byEj[19] ?? 0;
    $nombre = $dbAreas[$aId] ?? '?';
    echo "  DB[area_id=$aId] r2026=$r17 r2027=$r19 | $nombre\n";
}

// ── Construir mapeo por coincidencia de conteos ────────────────────────────────
echo "\n=== MAPEO PROPUESTO (JSON areaId → DB area_id) ===\n";
// Para cada área JSON, buscar el DB area_id cuyo conteo coincida
foreach ($jsonAreas as $jArea) {
    $r26 = $jsonRiskCount[$jArea][2026] ?? 0;
    $r27 = $jsonRiskCount[$jArea][2027] ?? 0;
    $candidates = [];
    foreach ($dbCount as $aId => $byEj) {
        if (($byEj[17] ?? 0) === $r26 && ($byEj[19] ?? 0) === $r27) {
            $candidates[] = "area_id=$aId ({$dbAreas[$aId]})";
        }
    }
    $match = empty($candidates) ? '❌ SIN MATCH' : implode(' | ', $candidates);
    echo "  $jArea (r26=$r26, r27=$r27) → $match\n";
}

// ── Proyectos existentes 2026/2027 ────────────────────────────────────────────
echo "\n=== TODOS proyectos 2026 (ej=17) ===\n";
$p26 = DB::table('proyectos')->where('ejercicio_id', 17)->select('proyecto_id','nombre')->get();
foreach ($p26 as $p) echo "  [{$p->proyecto_id}] {$p->nombre}\n";

echo "\n=== TODOS proyectos 2027 (ej=19) ===\n";
$p27 = DB::table('proyectos')->where('ejercicio_id', 19)->select('proyecto_id','nombre')->get();
foreach ($p27 as $p) echo "  [{$p->proyecto_id}] {$p->nombre}\n";
