<?php
$json = json_decode(file_get_contents('matrix.json'), true);
$counts = [];
foreach($json as $i) {
    $name = $i['nombre_area'];
    $counts[$name] = ($counts[$name] ?? 0) + 1;
}
foreach($counts as $name => $count) {
    echo "$name: $count\n";
}
