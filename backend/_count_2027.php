<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$c=0;
if (isset($j['poaActions'])) {
    foreach($j['poaActions'] as $a) {
        if(isset($a['exercise']) && $a['exercise'] == 2027) $c++;
    }
}
echo "Total 2027 poaActions in JSON: $c\n";
