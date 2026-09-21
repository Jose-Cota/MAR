<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
echo 'Areas count in seed array: ' . count($seedData['areas']) . PHP_EOL;
