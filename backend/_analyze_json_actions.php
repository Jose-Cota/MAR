<?php
$file = 'C:\\Cota\\MAR\\Respaldo_MAR_TECDMX_2026-09-23.json';
$data = json_decode(file_get_contents($file), true);

$firstRisk = $data['risks'][0];
echo "Risk localId: " . $firstRisk['localId'] . "\n";
echo "Linked actions: " . implode(', ', $firstRisk['linkedActionIds']) . "\n";

foreach ($data['poaActions'] as $action) {
    if (in_array($action['id'], $firstRisk['linkedActionIds'])) {
        echo "Found linked action in poaActions:\n";
        print_r($action);
    }
}
