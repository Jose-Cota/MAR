<?php
$json = file_get_contents('c:/cota/MAR/Ultimo-Respaldo_MAR_TECDMX_2026-09-24.json');
$data = json_decode($json, true);
if(isset($data['poa2027MasterMatrixSource'])) {
    echo "Keys in poa2027MasterMatrixSource:\n" . implode("\n", array_keys($data['poa2027MasterMatrixSource'])) . "\n";
} else {
    echo "No poa2027MasterMatrixSource\n";
}
