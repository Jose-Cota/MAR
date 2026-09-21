<?php
$seedJson = file_get_contents('C:/Cota/MAR/backend/seed.json');
$seed = json_decode($seedJson, true);
$count2026 = 0;
foreach ($seed['risks'] as $r) {
    if ($r['exercise'] == 2026) $count2026++;
}
echo 'Risks in seed for 2026: ' . $count2026 . PHP_EOL;
