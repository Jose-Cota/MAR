<?php
$ejercicios = DB::table('ejercicios')->get();
print_r($ejercicios);
$ej_id_2027 = DB::table('ejercicios')->where('ejercicio', 2027)->value('ejercicio_id');
echo 'ej_id_2027: ' . $ej_id_2027 . PHP_EOL;

$ej_id_2026 = DB::table('ejercicios')->where('ejercicio', 2026)->value('ejercicio_id');
echo 'ej_id_2026: ' . $ej_id_2026 . PHP_EOL;

echo 'Riesgos totales (all): ' . DB::table('riesgos')->count() . PHP_EOL;
echo 'Riesgos 2027 count: ' . DB::table('riesgos')->where('ejercicio_id', $ej_id_2027)->count() . PHP_EOL;
echo 'Riesgos 2026 count: ' . DB::table('riesgos')->where('ejercicio_id', $ej_id_2026)->count() . PHP_EOL;
