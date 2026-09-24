<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/(const LABELS=.*?;)/', $html, $m);
if(isset($m[1])) echo substr($m[1], 0, 500);
