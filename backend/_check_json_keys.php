<?php
$json = file_get_contents('c:/cota/MAR/Ultimo-Respaldo_MAR_TECDMX_2026-09-24.json');
$data = json_decode($json, true);
if(is_array($data)) {
    echo "Keys: " . implode(', ', array_keys($data)) . "\n";
} else {
    echo "Invalid JSON or not an object\n";
}
