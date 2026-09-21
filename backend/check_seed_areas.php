<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
$names = [];
foreach ($seedData['areas'] as $a) {
    $name = trim($a['urName'] ?? $a['name']);
    if (strpos(strtolower($name), 'pleno') !== false) $name = 'Pleno';
    $names[] = $name;
}
echo 'Unique areas in seed: ' . count(array_unique($names)) . PHP_EOL;
$uniqueNames = array_unique($names);
foreach($uniqueNames as $un) echo $un . PHP_EOL;
