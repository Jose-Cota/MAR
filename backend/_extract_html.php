<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
echo "Size: " . strlen($html) . "\n";
preg_match_all('/var\s+riesgos\s*=\s*(\[.*?\]);/s', $html, $m1);
preg_match_all('/var\s+acciones2026\s*=\s*(\[.*?\]);/s', $html, $m2);
preg_match_all('/var\s+acciones2027\s*=\s*(\[.*?\]);/s', $html, $m3);
preg_match_all('/var\s+poaActions\s*=\s*(\[.*?\]);/s', $html, $m4);

echo "Riesgos matches: " . count($m1[0]) . "\n";
echo "Acciones 26 matches: " . count($m2[0]) . "\n";
echo "Acciones 27 matches: " . count($m3[0]) . "\n";
echo "poaActions matches: " . count($m4[0]) . "\n";

if (isset($m4[1][0])) {
    $actions = json_decode($m4[1][0], true);
    if ($actions) {
        $c2027 = 0;
        foreach ($actions as $a) {
            if (isset($a['exercise']) && $a['exercise'] == 2027) $c2027++;
        }
        echo "Valid poaActions JSON. Found " . count($actions) . " actions total, $c2027 for 2027.\n";
    }
}
