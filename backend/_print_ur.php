<?php
$data = json_decode(file_get_contents('C:\Cota\MAR\backend\_todas_las_ur.json'), true);
foreach ($data as $area) {
    echo "\n=== " . $area['nombre'] . " ===\n";
    echo "Riesgos (" . count($area['riesgos2027']) . "):\n";
    foreach ($area['riesgos2027'] as $r) {
        echo " - " . $r['riesgo'] . "\n";
    }
    echo "Proyectos (" . count($area['proyectos2027']) . "):\n";
    foreach ($area['proyectos2027'] as $p) {
        echo " > " . $p['nombre'] . " (" . count($p['acciones_largas']) . " acciones largas)\n";
    }
}
