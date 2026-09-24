<?php
$json = file_get_contents('c:/cota/MAR/poa_2027_parchado.json');
$data = json_decode($json, true);
$sa = $data['SA'] ?? null;
if($sa) {
    echo "SA Activities:\n";
    foreach($sa['acts'] as $a) {
        echo "- " . $a['act'] . "\n";
        echo "   Riesgos: " . implode(', ', $a['riesgos']) . "\n";
    }
}
