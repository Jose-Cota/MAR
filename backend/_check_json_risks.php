<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$risks2027 = array_filter($data['risks'] ?? [], function($r) { return strpos($r['id'], '2027') !== false; });
$emptyLinks = array_filter($risks2027, function($r) { return empty($r['linkedActionIds']); });
echo '2027 Risks total: ' . count($risks2027) . "\n";
echo 'Empty links: ' . count($emptyLinks) . "\n";
