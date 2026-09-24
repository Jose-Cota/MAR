<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
file_put_contents('C:\Cota\MAR\backend\_html_start.txt', substr($h, 0, 1000));
