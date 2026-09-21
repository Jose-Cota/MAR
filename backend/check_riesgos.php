<?php
$riesgos = DB::table('riesgos')->where('ejercicio_id', 19)->count();
echo 'Riesgos with ID 19: ' . $riesgos . PHP_EOL;
$riesgos2027 = DB::table('riesgos')->where('ejercicio_id', 2027)->count();
echo 'Riesgos with ID 2027: ' . $riesgos2027 . PHP_EOL;
