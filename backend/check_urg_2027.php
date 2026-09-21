<?php
$c = DB::table('unidades_responsables_gastos')->where('ejercicio_id', 19)->count();
echo 'Areas for 2027: ' . $c . PHP_EOL;
