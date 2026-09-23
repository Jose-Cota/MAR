<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
echo substr(json_encode($data['poa2027MasterMatrixSource'] ?? []), 0, 1000);
