<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

$jsonCounts = [];
foreach ($data['poaActions'] as $action) {
    if (strpos($action['id'], '-2027-') !== false) {
        $parts = explode('-', $action['id']);
        $area = $parts[0];
        if (!isset($jsonCounts[$area])) $jsonCounts[$area] = 0;
        $jsonCounts[$area]++;
    }
}

echo "=== JSON COUNTS 2027 ===\n";
foreach ($jsonCounts as $area => $count) {
    echo "$area: $count\n";
}
