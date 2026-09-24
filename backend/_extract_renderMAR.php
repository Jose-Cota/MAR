<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/function renderMAR\(.*?\{.*?\n\}/s', $html, $m);
if(isset($m[0])) {
    file_put_contents('C:\Cota\MAR\backend\_renderMAR.txt', substr($m[0], 0, 2000));
}
