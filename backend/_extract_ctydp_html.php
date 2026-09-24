<?php
$html = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
preg_match('/const M = \{.*?db: \{(.*?)\},\s*ui:/s', $html, $m);
if(isset($m[1])) {
    $db_string = '{' . $m[1] . '}';
    // Not valid JSON due to unquoted keys, etc. Let's just find "linkedActionIds" in risks
    preg_match_all('/"id":"CTyDP-2027-[^"]*".*?"linkedActionIds":\[(.*?)\]/', $html, $matches);
    print_r($matches[1]);
}
