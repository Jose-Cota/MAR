<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'M.seedDatabase');
if ($pos !== false) {
    echo "Found M.seedDatabase at $pos\n";
    // Let's find where M.seedDatabase is defined
    $defPos = strpos($h, 'M.seedDatabase=', max(0, $pos - 10000));
    if ($defPos === false) $defPos = strpos($h, 'M.seedDatabase =', max(0, $pos - 10000));
    if ($defPos !== false) {
        file_put_contents('C:\Cota\MAR\backend\_seed.js', substr($h, $defPos, 200000));
        echo "Wrote seed.js\n";
    } else {
        echo "Could not find definition of M.seedDatabase\n";
    }
}
