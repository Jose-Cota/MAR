<?php
$seedData = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
var_dump(array_slice($seedData['areas'], 20));
