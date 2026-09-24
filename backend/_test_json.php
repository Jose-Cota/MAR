<?php
$db = json_decode(file_get_contents('C:\Cota\MAR\backend\respaldo.json'), true);
$c26 = 0; $c27 = 0;
foreach ($db['poaActions'] as $a) {
    if ($a['exercise'] == 2026) $c26++;
    if ($a['exercise'] == 2027) $c27++;
}
echo "2026 acciones: $c26\n";
echo "2027 acciones: $c27\n";
