<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\backend\_todas_las_ur.json'), true);
foreach($j as $a) {
    echo "ID: " . $a['id'] . " - " . substr($a['nombre'], 0, 30) . " | R: " . count($a['riesgos2027']) . " | A26: " . count($a['acciones2026']) . "\n";
}
