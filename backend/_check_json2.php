<?php
$s = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
foreach($s['poaActions'] as $a) {
    if($a['areaId'] == 'PKSL') {
        echo "- " . $a['text'] . "\n";
    }
}
