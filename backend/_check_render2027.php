<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/function render2027\(\)\{(.*?)\}/s', $html, $matches);
if(isset($matches[1])) echo substr($matches[1], 1000, 1000);
