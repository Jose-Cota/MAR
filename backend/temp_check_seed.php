<?php
$s=json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
foreach(array_slice($s['risks'], 0, 10) as $r) {
    echo $r['localId'] . "\n";
}
