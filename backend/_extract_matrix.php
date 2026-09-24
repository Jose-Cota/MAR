<?php
$html = file_get_contents('c:/cota/MAR/Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/const\s+MAR_V66_POA2027_MASTER_MATRIX\s*=\s*(\{.*?\});/s', $html, $matches);
if (isset($matches[1])) {
    $jsonString = $matches[1];
    // This is JS syntax, not strict JSON. We need to parse it or convert it.
    // The easiest way is to use a node.js script to extract it, or do a regex.
    file_put_contents('matrix_temp.js', "const fs = require('fs');\nconst MAR_V66_POA2027_MASTER_MATRIX = " . $jsonString . ";\nfs.writeFileSync('matrix_temp.json', JSON.stringify(MAR_V66_POA2027_MASTER_MATRIX));");
    echo "Extracted JS to matrix_temp.js\n";
} else {
    echo "No matrix found\n";
}
