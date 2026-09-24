<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
foreach($j['poaProjects'] as $p) {
    if(isset($p['exercise']) && $p['exercise'] == 2027 && $p['areaId'] == 'CTyDP') {
        echo "Name: {$p['name']}\nDesc: {$p['description']}\n";
    }
}
