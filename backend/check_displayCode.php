<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
foreach ($seedData['areas'] as $a) {
    echo ($a['displayCode'] ?? 'no-code') . ' - ' . ($a['name']) . PHP_EOL;
}
