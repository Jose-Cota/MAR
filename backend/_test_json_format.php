<?php
$jsonStr = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($jsonStr, true);
echo "Keys: " . implode(', ', array_keys($data)) . "\n";
echo "Areas count: " . count($data['areas']) . "\n";
echo "Projects count: " . count($data['poaProjects']) . "\n";
echo "Actions count: " . count($data['poaActions']) . "\n";
echo "Risks count: " . count($data['risks']) . "\n";
