<?php
$json = file_get_contents('c:/cota/MAR/Ultimo-Respaldo_MAR_TECDMX_2026-09-24.json');
$data = json_decode($json, true);

if(isset($data['poa2027'])) {
    foreach($data['poa2027'] as $py) {
        if($py['urg_nombre'] == 'Secretaría Administrativa' || strpos($py['urg_nombre'], 'Administrativa') !== false) {
            echo "Encontrada Secretaría Administrativa en JSON!\n";
            echo "Actividades: " . count($py['acciones']) . "\n";
            foreach($py['acciones'] as $a) {
                echo " - " . $a['descripcion'] . "\n";
                $riesgos = [];
                foreach($a['riesgos_vinculados'] as $r) {
                    $riesgos[] = $r['local_id'] ?? $r['riesgo'] ?? '?';
                }
                echo "   Riesgos: " . implode(', ', $riesgos) . "\n";
            }
        }
    }
} else {
    echo "No hay poa2027 en el JSON.\n";
}
