<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/risks\s*:\s*\[(.*?)\],\s*indicators/s', $html, $m);
if(isset($m[1])) {
    file_put_contents('C:\Cota\MAR\backend\_html_risks.json', '[' . $m[1] . ']');
    echo "Saved to _html_risks.json\n";
} else {
    echo "Could not find risks in HTML.\n";
}
