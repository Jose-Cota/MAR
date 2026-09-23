<?php
$jsonStr = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($jsonStr, true);
$projects = $data['poaProjects'] ?? [];
$actions = $data['poaActions'] ?? [];

echo "Looking for Planeación...\n";
foreach ($projects as $p) {
    if (strpos(mb_strtolower($p['name']), 'planeación y recursos') !== false && $p['exercise'] == 2026) {
        print_r($p);
        foreach ($actions as $a) {
            if ($a['projectId'] === $p['id']) {
                print_r($a);
            }
        }
    }
}
