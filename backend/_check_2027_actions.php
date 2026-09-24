<?php
$j = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$actions2027 = array_filter($j['poaActions'], function($a) { return $a['exercise'] == 2027; });
echo count($actions2027);
