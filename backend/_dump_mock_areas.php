<?php
$mock = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);
foreach($mock['areas'] as $a) {
    echo $a['id'] . ' -> ' . $a['name'] . "\n";
}
