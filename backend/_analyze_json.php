<?php
$file = 'C:\\Cota\\MAR\\Respaldo_MAR_TECDMX_2026-09-23.json';
$data = json_decode(file_get_contents($file), true);

echo "Keys at root: " . implode(', ', array_keys($data)) . "\n";
echo "Count risks: " . count($data['risks']) . "\n";
if (isset($data['risks'][0])) {
    echo "Sample risk keys: " . implode(', ', array_keys($data['risks'][0])) . "\n";
    print_r($data['risks'][0]);
}
