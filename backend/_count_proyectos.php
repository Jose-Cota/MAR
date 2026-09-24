<?php
$data = json_decode(file_get_contents('_todas_las_ur.json'), true);
$c = 0;
foreach($data as $d) {
    $c += count($d['proyectos2027']);
}
echo 'Proyectos 2027 totales: ' . $c . "\n";
