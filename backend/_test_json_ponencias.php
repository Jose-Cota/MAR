<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

foreach ($data['riesgos'] as $r) {
    if (isset($r['area']) && stripos($r['area'], 'Ambriz') !== false) {
        if ($r['ejercicio'] == '2027') {
            echo "AMBRIZ 2027: " . $r['id'] . " - " . $r['riesgo'] . "\n";
            if (isset($r['actividades'])) {
                foreach ($r['actividades'] as $a) {
                    echo "  - Act: " . $a . "\n";
                }
            } else {
                echo "  - NO ACTIVIDADES LINKED IN JSON\n";
            }
        }
    }
    if (isset($r['area']) && stripos($r['area'], 'José Jesús Hernández') !== false) {
        if ($r['ejercicio'] == '2027') {
            echo "HERNANDEZ 2027: " . $r['id'] . " - " . $r['riesgo'] . "\n";
            if (isset($r['actividades'])) {
                foreach ($r['actividades'] as $a) {
                    echo "  - Act: " . $a . "\n";
                }
            } else {
                echo "  - NO ACTIVIDADES LINKED IN JSON\n";
            }
        }
    }
}
