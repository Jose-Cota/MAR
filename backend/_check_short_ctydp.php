<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
foreach($j['poaActions'] as $a) {
    if($a['areaId'] == 'CTyDP' && strlen($a['text']) < 100) {
        echo "Exercise {$a['exercise']} - {$a['id']}: {$a['text']}\n";
    }
}
