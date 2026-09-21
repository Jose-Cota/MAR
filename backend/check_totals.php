<?php
$areas = DB::table('unidades_responsables_gastos')->count();
echo 'Total areas in DB: ' . $areas . PHP_EOL;
$riesgos = DB::table('riesgos')->count();
echo 'Total riesgos in DB: ' . $riesgos . PHP_EOL;
$riesgos2027 = DB::table('riesgos')->where('ejercicio_id', 19)->count();
echo 'Total riesgos in DB 2027: ' . $riesgos2027 . PHP_EOL;
