<?php
$seedJson = file_get_contents('C:/Cota/MAR/backend/seed.json');
$seed = json_decode($seedJson, true);
$count2027 = 0;
foreach ($seed['risks'] as $r) {
    if ($r['exercise'] == 2027) $count2027++;
}
echo 'Risks in seed for 2027: ' . $count2027 . PHP_EOL;
