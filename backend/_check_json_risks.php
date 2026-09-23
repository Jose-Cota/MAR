<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
print_r(array_slice($data['risks'] ?? [], 0, 1));
