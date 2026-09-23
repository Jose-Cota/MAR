<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$startStr = 'const SEED = {"schemaVersion"';
$startIdx = strpos($html, $startStr);
if ($startIdx !== false) {
    $startIdx += strlen('const SEED = ');
    // Find where the JSON ends by counting braces
    $braceCount = 0;
    $inString = false;
    $escape = false;
    $endIdx = -1;
    for ($i = $startIdx; $i < strlen($html); $i++) {
        $c = $html[$i];
        if ($escape) {
            $escape = false;
            continue;
        }
        if ($c === '\\') {
            $escape = true;
            continue;
        }
        if ($c === '"') {
            $inString = !$inString;
            continue;
        }
        if (!$inString) {
            if ($c === '{') $braceCount++;
            if ($c === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    $endIdx = $i;
                    break;
                }
            }
        }
    }
    
    if ($endIdx !== -1) {
        $jsonStr = substr($html, $startIdx, $endIdx - $startIdx + 1);
        $seed = json_decode($jsonStr, true);
        if ($seed) {
            file_put_contents('C:\Cota\MAR\extracted_seed.json', json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "Extracted SEED to extracted_seed.json. Contains " . count($seed['poaProjects'] ?? []) . " projects.\n";
            echo "Backup Date: " . ($seed['backupDate'] ?? 'unknown') . "\n";
        } else {
            echo "JSON DECODE FAILED!\n";
            echo json_last_error_msg() . "\n";
        }
    } else {
        echo "Could not find end of JSON.\n";
    }
} else {
    echo "Could not find SEED.\n";
}
