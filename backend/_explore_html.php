<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$doc = new DOMDocument();
@$doc->loadHTML($html);

$xpath = new DOMXPath($doc);
// Usually the data is in some table, or div structure. 
// Let's print out some sample nodes or elements that look like projects or URs.

// Check if there are tables
$tables = $xpath->query('//table');
echo "Found " . $tables->length . " tables.\n";

// Just output a small snippet of the body text to understand the layout
$body = $xpath->query('//body')->item(0);
echo substr($body->nodeValue, 0, 500) . "...\n";

// Maybe there is a script tag with JSON data embedded?
$scripts = $xpath->query('//script');
foreach($scripts as $s) {
    if (strpos($s->nodeValue, 'const') !== false || strpos($s->nodeValue, 'var') !== false) {
        if (strlen($s->nodeValue) > 1000) {
            echo "Found a large script block! Length: " . strlen($s->nodeValue) . "\n";
            // Check for clues of JSON data
            if (strpos($s->nodeValue, 'fichas') !== false || strpos($s->nodeValue, 'proyectos') !== false || strpos($s->nodeValue, 'poa') !== false) {
                echo "Looks like it contains poa data.\n";
            }
        }
    }
}
