<?php
$db = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);
foreach($db['poaActions'] as $a) {
    if (in_array($a['id'], ["POA2027-020603-A1", "POA2027-030704-A1"])) {
        print_r($a);
    }
}
