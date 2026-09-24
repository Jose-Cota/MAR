<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$start = strpos($h, 'poaActions:[');
if ($start !== false) {
    // Find the end bracket
    $brackets = 0;
    $end = -1;
    $inString = false;
    for ($i = $start + 11; $i < strlen($h); $i++) {
        $c = $h[$i];
        if ($c == '"' && $h[$i-1] != '\\') $inString = !$inString;
        if (!$inString) {
            if ($c == '[') $brackets++;
            if ($c == ']') {
                $brackets--;
                if ($brackets == 0) {
                    $end = $i;
                    break;
                }
            }
        }
    }
    
    if ($end != -1) {
        $json = substr($h, $start + 11, $end - ($start + 11) + 1);
        file_put_contents('C:\Cota\MAR\backend\_poa_actions_clean2.json', $json);
        echo "Extracted poaActions successfully: " . strlen($json) . " bytes\n";
    }
} else {
    echo "Could not find poaActions:[\n";
}
