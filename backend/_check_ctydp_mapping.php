<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$risks2027 = array_filter($j['risks'], function($r) { return $r['exercise'] == 2027 && $r['areaId'] === 'CTyDP'; });
foreach ($risks2027 as $r) {
    echo $r['localId'] . " -> " . implode(', ', $r['linkedActionIds']) . "\n";
}
