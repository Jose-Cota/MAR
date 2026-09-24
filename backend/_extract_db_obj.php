<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, '{users:[');
if ($pos !== false) {
    echo "Found db object at $pos\n";
    file_put_contents('C:\Cota\MAR\backend\_db_obj.js', substr($h, $pos - 20, 500000));
    echo "Wrote to _db_obj.js\n";
} else {
    echo "Could not find {users:[ \n";
    // Try { areas: [
    $pos = strpos($h, 'areas:[');
    if ($pos !== false) {
        echo "Found areas:[ at $pos\n";
        file_put_contents('C:\Cota\MAR\backend\_db_obj.js', substr($h, $pos - 20, 500000));
    }
}
