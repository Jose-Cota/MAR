<?php
$data = json_decode(file_get_contents('_todas_las_ur.json'), true);
print_r($data[0]);
