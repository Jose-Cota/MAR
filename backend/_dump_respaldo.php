<?php
$db = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);

echo "AREAS:\n";
foreach(array_slice($db['areas'], 0, 5) as $a) {
    echo $a['id'] . " - " . $a['name'] . "\n";
}

echo "\nPOAACTIONS (first 5 of 2027):\n";
$c=0;
foreach($db['poaActions'] as $a) {
    if ($a['exercise'] == 2027) {
        echo $a['areaId'] . " - " . $a['text'] . "\n";
        $c++;
        if ($c==5) break;
    }
}

echo "\nRISKS (first 5):\n";
foreach(array_slice($db['risks'], 0, 5) as $r) {
    echo $r['id'] . " - " . $r['areaId'] . " - poaActions: " . implode(',', $r['poaActionIds'] ?? []) . "\n";
}
