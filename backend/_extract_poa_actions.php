<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/poaActions\s*:\s*\[(.*?)\],\s*risks/s', $html, $m);
if(isset($m[1])) {
    file_put_contents('C:\Cota\MAR\backend\_html_poa_actions.json', '[' . $m[1] . ']');
    echo "Saved to _html_poa_actions.json\n";
} else {
    echo "Could not find poaActions in HTML.\n";
}
