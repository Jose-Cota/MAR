<?php
/**
 * _update_2027_texts.php
 * Actualiza los textos de las acciones_sustantivas de 2027 usando el JSON parchado del HTML.
 */
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Mismo mapeo de áreas usado anteriormente
$AREA_MAP = [
    'PRES'    => ['py2027' => [1767]],
    'SG'      => ['py2027' => [1768, 1769, 1770]],
    'SA'      => ['py2027' => [1771]],
    'DPyRF'   => ['py2027' => [1772]],
    'DRH'     => ['py2027' => [1773]],
    'DRMySG'  => ['py2027' => [1774]],
    'CI'      => ['py2027' => [1775, 1776, 1777, 1778, 1779]],
    'DGJ'     => ['py2027' => [1780, 1781, 1782]],
    'CCSyRP'  => ['py2027' => [1783, 1784]],
    'CTyDP'   => ['py2027' => [1803]],
    'IFyC'    => ['py2027' => [1788]],
    'CCLA'    => ['py2027' => [1789]],
    'USI'     => ['py2027' => [1790, 1791]],
    'UEyJ'    => ['py2027' => [1792, 1793]],
    'CDyP'    => ['py2027' => [1794, 1795, 1796]],
    'CA'      => ['py2027' => [1797]],
    'CDHyG'   => ['py2027' => [1798]],
    'DPPCyPD' => ['py2027' => [1799]],
    'CVyRI'   => ['py2027' => [1800, 1802]],
    'UEPS'    => ['py2027' => [1801]],
    'PAAH'    => ['py2027' => [1757, 1758]],
    'PJHR'    => ['py2027' => [1759, 1760]],
    'POVR'    => ['py2027' => [1761, 1762]],
    'PKSL'    => ['py2027' => [1763, 1764]],
    'PLPJC'   => ['py2027' => [1765, 1766]],
];

echo "Cargando poa_2027_parchado.json...\n";
$jsonPath = __DIR__ . '/../poa_2027_parchado.json';
if (!file_exists($jsonPath)) die("No se encontró $jsonPath\n");
$json = json_decode(file_get_contents($jsonPath), true);
$actions = $json['actions'] ?? [];

echo "Total acciones parchadas encontradas: " . count($actions) . "\n\n";

$actualizadas = 0;
DB::beginTransaction();
try {
    foreach ($actions as $act) {
        $areaId = $act['areaId'];
        $num = $act['number'];
        $newText = $act['text'];

        if (!isset($AREA_MAP[$areaId])) continue;
        
        $proyIds = $AREA_MAP[$areaId]['py2027'];
        if (empty($proyIds)) continue;

        // Las importamos en el primer proyecto de la lista, igual que en el script de importación
        $proyPrincipal = $proyIds[0];

        $affected = DB::table('acciones_sustantivas')
            ->where('proyecto_id', $proyPrincipal)
            ->where('numero', $num)
            ->update(['descripcion' => $newText]);

        $actualizadas += $affected;
    }
    DB::commit();
    echo "✅ EXITO! Actividades de 2027 actualizadas: $actualizadas\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
