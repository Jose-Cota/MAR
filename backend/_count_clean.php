<?php
$a = json_decode(file_get_contents('C:\Cota\MAR\backend\poa_actions_clean.json'), true);
$c26 = 0; $c27 = 0;
if ($a) {
    foreach ($a as $x) {
        if ($x['exercise'] == 2026) $c26++;
        if ($x['exercise'] == 2027) $c27++;
    }
} else {
    echo "JSON decode failed: " . json_last_error_msg() . "\n";
}
echo "2026 actions: $c26\n";
echo "2027 actions: $c27\n";
// Dump some 2027 actions
$c = 0;
foreach ($a as $x) {
    if ($x['exercise'] == 2027) {
        echo $x['areaId'] . " - " . $x['text'] . "\n";
        $c++;
        if ($c > 5) break;
    }
}
