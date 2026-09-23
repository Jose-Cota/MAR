<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);

foreach ($data['risks'] as $risk) {
    if ($risk['exercise'] == 2027) {
        echo "2027 Risk localId: " . $risk['localId'] . "\n";
        echo "Linked actions: " . implode(', ', $risk['linkedActionIds'] ?? []) . "\n";
        break;
    }
}

// Find PRES-2027-A3 in any JSON key
foreach (['poaActions', 'poaGoals', 'poaIndicators', 'poa2027MasterMatrixSource'] as $key) {
    if (!isset($data[$key])) continue;
    foreach ($data[$key] as $item) {
        if (isset($item['id']) && strpos($item['id'], '2027-A') !== false) {
            echo "Found 2027 action in $key:\n";
            print_r($item);
            break;
        }
    }
}
