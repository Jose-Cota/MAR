<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

echo "PLPJC 2026 Actions in JSON:\n";
foreach ($data['poaActions'] as $action) {
    if (strpos($action['id'], 'PLPJC-2026-') !== false) {
        // Find which project this action belongs to
        $projId = "UNKNOWN";
        foreach ($data['poaProjects'] as $p) {
            if (in_array($action['id'], $p['actionIds'] ?? [])) {
                $projId = $p['id'];
                break;
            }
        }
        echo $action['id'] . " -> Proj: $projId | " . substr($action['description'], 0, 50) . "\n";
    }
}
