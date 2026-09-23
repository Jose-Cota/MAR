<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
print_r(array_keys($data['poaProjects'][0]));
print_r($data['poaProjects'][0]['activities'][0] ?? null);
