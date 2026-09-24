<?php
$db = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);
echo "\nRISKS (first 5 of 2027):\n";
$c=0;
foreach($db['risks'] as $r) {
    if ($r['exercise'] == 2027) {
        echo $r['id'] . " - " . $r['areaId'] . " - poaActionIds: " . json_encode($r['poaActionIds'] ?? []) . " - linkedActionIds: " . json_encode($r['linkedActionIds'] ?? []) . "\n";
        $c++;
        if ($c==5) break;
    }
}
