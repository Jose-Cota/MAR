<?php
$json = file_get_contents('c:/cota/MAR/Ultimo-Respaldo_MAR_TECDMX_2026-09-24.json');
$data = json_decode($json, true);
echo substr($data['poa2027MasterMatrixSource'], 0, 1000) . "\n";
