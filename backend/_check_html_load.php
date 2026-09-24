<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$lines = explode("\n", $html);
foreach($lines as $i => $line) {
    if(strpos($line, 'function loadPOAActions') !== false || strpos($line, 'function render') !== false || strpos($line, 'poaActions') !== false) {
        if(strpos($line, 'CTyDP') !== false || strpos($line, 'function ') !== false) {
            echo "Line $i: ". substr($line, 0, 100) . "\n";
        }
    }
}
