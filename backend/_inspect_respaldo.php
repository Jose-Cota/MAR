<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

echo "Keys at root:\n";
print_r(array_keys($data));

if (isset($data['projects'])) {
    echo "\nSample project:\n";
    print_r($data['projects'][0]);
}

if (isset($data['risks'])) {
    echo "\nSample risk:\n";
    print_r($data['risks'][0]);
}
