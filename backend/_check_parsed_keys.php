<?php
$json = file_get_contents('c:/cota/MAR/poa_2027_parchado.json');
$data = json_decode($json, true);
echo "Keys: " . implode(', ', array_keys($data)) . "\n";
