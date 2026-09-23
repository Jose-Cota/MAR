<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$prefixes = [];
foreach($data['risks'] as $r) {
    if(($r['exercise'] ?? 0) == 2027 || strpos($r['id'], '2027') !== false) {
        $prefixes[] = explode('-', $r['id'])[0];
    }
}
print_r(array_count_values($prefixes));
