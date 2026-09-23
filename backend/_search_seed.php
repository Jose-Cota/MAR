<?php
$s = json_decode(file_get_contents('C:\Cota\MAR\extracted_seed.json'), true);
foreach($s['poaActions'] as $a) {
    if(strpos(mb_strtolower($a['text']), 'coordinación de la administración') !== false) {
        print_r($a);
    }
}
