<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'M.db');
if ($pos !== false) {
    echo "Found M.db at $pos\n";
    $start = strpos($h, '{', $pos);
    // Let's just write the whole substring from M.db to the end into a file and then inspect it
    file_put_contents('C:\Cota\MAR\backend\_mdb.js', substr($h, $pos, 2000000));
    echo "Wrote to _mdb.js\n";
}
