<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match_all('/(const|var|let)\s+([a-zA-Z0-9_]+)\s*=\s*\[/i', substr($h, 0, 500000), $m);
if (!empty($m[2])) {
    print_r(array_unique($m[2]));
} else {
    echo "No matches found.";
}
