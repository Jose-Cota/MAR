<?php
$h = file_get_contents('C:\Cota\MAR\backend\_poa_actions.js');
$start = strpos($h, '[');

$brackets = 0;
$end = -1;
$inString = false;
for ($i = $start; $i < strlen($h); $i++) {
    $c = $h[$i];
    if ($c == '"' && $h[$i-1] != '\\') $inString = !$inString;
    if (!$inString) {
        if ($c == '[') $brackets++;
        if ($c == ']') {
            $brackets--;
            if ($brackets == 0) {
                $end = $i;
                break;
            }
        }
    }
}

$json = substr($h, $start, $end - $start + 1);
file_put_contents('C:\Cota\MAR\backend\_poa_actions_clean3.json', $json);
echo "Extracted array length: " . strlen($json) . "\n";
$arr = json_decode($json, true);
if ($arr) {
    echo "Decoded items: " . count($arr) . "\n";
    $c27 = 0;
    foreach ($arr as $item) if ($item['exercise'] == 2027) $c27++;
    echo "Items for 2027: $c27\n";
} else {
    echo "Invalid JSON: " . json_last_error_msg() . "\n";
}
