<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
foreach($j['poaActions'] as $a) {
    if(isset($a['exercise']) && $a['exercise'] == 2027 && ($a['areaId'] == '10' || $a['areaId'] == 'CTyDP')) {
        echo $a['text'] . "\n";
    }
}
