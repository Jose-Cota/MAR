<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$lines = explode("\n", $html);
foreach($lines as $i => $line) {
    if(strpos($line, 'Solicitudes de acceso a la informaci') !== false) {
        echo "Line $i: $line\n";
    }
}
