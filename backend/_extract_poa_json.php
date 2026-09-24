<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'poaActions:[');
$end = strpos($h, ']', $pos);

$json = substr($h, $pos + 11, $end - $pos - 10);
file_put_contents('C:\Cota\MAR\backend\poa_actions.json', $json);
echo "Wrote poa_actions.json size: " . strlen($json) . "\n";
