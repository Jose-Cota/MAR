<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'poaActions');
if ($pos !== false) {
    echo "Found poaActions at $pos\n";
    file_put_contents('C:\Cota\MAR\backend\_poa_actions.js', substr($h, $pos, 50000));
} else {
    echo "Not found.\n";
}
