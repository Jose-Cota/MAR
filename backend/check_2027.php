<?php
$ej_id = DB::table('ejercicios')->where('ejercicio', 2027)->value('ejercicio_id');
echo 'Riesgos en 2027: ' . DB::table('riesgos')->where('ejercicio_id', $ej_id)->count() . PHP_EOL;
echo 'Areas distintas en 2027: ' . DB::table('riesgos')->where('ejercicio_id', $ej_id)->distinct('area_id')->count('area_id') . PHP_EOL;
