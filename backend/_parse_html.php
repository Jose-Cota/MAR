<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$dom = new DOMDocument();
@$dom->loadHTML($h);
$tables = $dom->getElementsByTagName('table');
echo "Found " . $tables->length . " tables.\n";
$firstTableText = "";
if ($tables->length > 0) {
    $firstTableText = substr($tables->item(0)->textContent, 0, 500);
    echo "Table 0 text: $firstTableText\n";
}
// Try finding text like "POA y acciones sustantivas"
$pos = strpos($h, 'POA y acciones sustantivas');
if ($pos !== false) {
    echo "Found 'POA y acciones sustantivas' at $pos\n";
    echo substr($h, $pos, 500) . "\n";
}
