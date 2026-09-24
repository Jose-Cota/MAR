<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

if (isset($data[0])) {
    echo "It's an array of objects. First object keys:\n";
    print_r(array_keys($data[0]));
    
    foreach ($data as $r) {
        if (isset($r['Unidad Responsable']) && stripos($r['Unidad Responsable'], 'Hernández') !== false) {
            echo "HERNANDEZ: " . ($r['ID Riesgo'] ?? '') . "\n";
            print_r($r);
            break;
        }
    }
} else {
    echo "Keys:\n";
    print_r(array_keys($data));
}
