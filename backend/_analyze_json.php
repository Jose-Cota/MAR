<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

$risks2027 = array_filter($j['risks'], function($r) { return $r['exercise'] == 2027; });
echo "Total risks 2027: " . count($risks2027) . "\n";

$actionsMapped = [];
foreach ($risks2027 as $r) {
    if (isset($r['linkedActionIds']) && is_array($r['linkedActionIds'])) {
        foreach ($r['linkedActionIds'] as $aId) {
            $actionsMapped[$r['areaId']][] = $aId;
        }
    }
}

foreach ($actionsMapped as $area => $acts) {
    $actionsMapped[$area] = array_unique($acts);
}

echo "Areas with mapped actions in 2027:\n";
foreach ($actionsMapped as $area => $acts) {
    echo "  $area: " . count($acts) . " actions\n";
}
