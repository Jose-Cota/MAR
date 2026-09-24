<?php
$json = file_get_contents('c:/cota/MAR/Ultimo-Respaldo_MAR_TECDMX_2026-09-24.json');
$data = json_decode($json, true);

if(isset($data['poa2027MasterMatrixSource'])) {
    $matrix = $data['poa2027MasterMatrixSource'];
    if(isset($matrix['Secretaría Administrativa'])) {
        $sa = $matrix['Secretaría Administrativa'];
        echo "SA Description:\n" . $sa['desc'] . "\n\n";
        echo "SA Activities:\n";
        foreach($sa['acts'] as $a) {
            echo "- " . $a['act'] . " (Riesgos: " . implode(', ', $a['riesgos']) . ")\n";
        }
    } else {
        echo "No SA inside poa2027MasterMatrixSource\n";
    }
} else {
    echo "No poa2027MasterMatrixSource in JSON\n";
}
