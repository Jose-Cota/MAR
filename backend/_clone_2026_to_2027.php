<?php
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$AREA_MAP = [
    'PRES'    => ['db_area_id' => 1,  'py2026' => [1589], 'py2027' => [1767]],
    'SG'      => ['db_area_id' => 7,  'py2026' => [1590], 'py2027' => [1768, 1769, 1770]],
    'SA'      => ['db_area_id' => 8,  'py2026' => [1591], 'py2027' => [1771]],
    'DPyRF'   => ['db_area_id' => 4,  'py2026' => [1592], 'py2027' => [1772]],
    'DRH'     => ['db_area_id' => 5,  'py2026' => [1593], 'py2027' => [1773]],
    'DRMySG'  => ['db_area_id' => 6,  'py2026' => [1594], 'py2027' => [1774]],
    'CI'      => ['db_area_id' => 9,  'py2026' => [1595], 'py2027' => [1775, 1776, 1777, 1778, 1779]],
    'DGJ'     => ['db_area_id' => 10, 'py2026' => [1596], 'py2027' => [1780, 1781, 1782]],
    'CCSyRP'  => ['db_area_id' => 14, 'py2026' => [1597], 'py2027' => [1783, 1784]],
    'CTyDP'   => ['db_area_id' => 15, 'py2026' => [1598], 'py2027' => [1803]],
    'IFyC'    => ['db_area_id' => 11, 'py2026' => [1599], 'py2027' => [1788]],
    'CCLA'    => ['db_area_id' => 17, 'py2026' => [1600], 'py2027' => [1789]],
    'USI'     => ['db_area_id' => 13, 'py2026' => [1601], 'py2027' => [1790, 1791]],
    'UEyJ'    => ['db_area_id' => 12, 'py2026' => [1602], 'py2027' => [1792, 1793]],
    'CDyP'    => ['db_area_id' => 16, 'py2026' => [1603], 'py2027' => [1794, 1795, 1796]],
    'CA'      => ['db_area_id' => 18, 'py2026' => [1604], 'py2027' => [1797]],
    'CDHyG'   => ['db_area_id' => 19, 'py2026' => [1605], 'py2027' => [1798]],
    'DPPCyPD' => ['db_area_id' => 20, 'py2026' => [1606], 'py2027' => [1799]],
    'CVyRI'   => ['db_area_id' => 3,  'py2026' => [1607], 'py2027' => [1800, 1802]],
    'UEPS'    => ['db_area_id' => 2,  'py2026' => [1608], 'py2027' => [1801]],
    'PAAH'    => ['db_area_id' => 21, 'py2026' => [1609, 1610], 'py2027' => [1757, 1758]],
    'PJHR'    => ['db_area_id' => 22, 'py2026' => [1611, 1612], 'py2027' => [1759, 1760]],
    'POVR'    => ['db_area_id' => 23, 'py2026' => [1613, 1614], 'py2027' => [1761, 1762]],
    'PKSL'    => ['db_area_id' => 24, 'py2026' => [1615, 1616], 'py2027' => [1763, 1764]],
    'PLPJC'   => ['db_area_id' => 25, 'py2026' => [1617, 1618], 'py2027' => [1765, 1766]],
];

DB::beginTransaction();
try {
    foreach ($AREA_MAP as $code => $cfg) {
        $proys26 = $cfg['py2026'];
        $proys27 = $cfg['py2027'];
        $proyPrin27 = $proys27[0];

        // 1. Delete all 2027 activities and links
        $acts27 = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proys27)->pluck('accion_sustantiva_id');
        if($acts27->count() > 0) {
            DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $acts27)->delete();
            DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proys27)->delete();
        }

        // 2. Fetch all 2026 activities
        $acts26 = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proys26)->get();

        // 3. Fetch 2027 risks for mapping
        $riesgos27 = DB::table('riesgos')->where('area_id', $cfg['db_area_id'])->where('ejercicio_id', 19)->get();
        $riesgoMap27 = [];
        foreach($riesgos27 as $r) {
            $riesgoMap27[$r->local_id] = $r->id;
        }

        // 4. Clone to 2027
        foreach($acts26 as $a26) {
            $newId = DB::table('acciones_sustantivas')->insertGetId([
                'proyecto_id' => $proyPrin27,
                'numero' => $a26->numero,
                'descripcion' => $a26->descripcion
            ]);

            // Link the same local_id risks from 2026
            $links26 = DB::table('actividad_riesgo')
                ->join('riesgos', 'riesgos.id', '=', 'actividad_riesgo.riesgo_id')
                ->where('actividad_sustantiva_id', $a26->accion_sustantiva_id)
                ->select('riesgos.local_id')
                ->get();
            
            foreach($links26 as $lk) {
                $locId = $lk->local_id;
                if(isset($riesgoMap27[$locId])) {
                    DB::table('actividad_riesgo')->insert([
                        'actividad_sustantiva_id' => $newId,
                        'riesgo_id' => $riesgoMap27[$locId],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
    DB::commit();
    echo "¡Las actividades de 2027 ahora son un espejo exacto de 2026 en TODAS las áreas!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
