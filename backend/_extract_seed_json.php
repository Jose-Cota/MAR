<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'M.seedDatabase');
if ($pos !== false) {
    // Look for return { ... }
    $retPos = strpos($h, 'return {', $pos);
    if ($retPos !== false) {
        $endPos = strpos($h, '};', $retPos);
        $jsonStr = substr($h, $retPos + 7, $endPos - $retPos - 6);
        file_put_contents('C:\Cota\MAR\backend\_seed_json.js', $jsonStr);
        echo "Extracted return { ... } from seedDatabase.\n";
    } else {
        // Maybe it's return{
        $retPos = strpos($h, 'return{', $pos);
        if ($retPos !== false) {
            $endPos = strpos($h, '};', $retPos);
            $jsonStr = substr($h, $retPos + 6, $endPos - $retPos - 5);
            file_put_contents('C:\Cota\MAR\backend\_seed_json.js', $jsonStr);
            echo "Extracted return{ ... } from seedDatabase.\n";
        }
    }
}
