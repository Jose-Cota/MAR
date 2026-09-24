<?php
foreach (['C:\Cota\MAR\scratch_seed.json', 'C:\Cota\MAR\extracted_seed.json'] as $file) {
    if (!file_exists($file)) continue;
    $j = json_decode(file_get_contents($file), true);
    if (!$j || !isset($j['poaActions'])) continue;
    $actions2027 = array_filter($j['poaActions'], function($a) { return $a['exercise'] == 2027; });
    echo basename($file) . " -> " . count($actions2027) . " actions\n";
}
