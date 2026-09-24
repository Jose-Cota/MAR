<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $h, $m);
foreach($m[1] as $i => $s) {
    if (strlen($s) > 1000) {
        echo "Script $i size: " . strlen($s) . "\n";
        file_put_contents("C:\\Cota\\MAR\\backend\\_script_$i.js", $s);
    }
}
