<?php
$areas = DB::table('unidades_responsables_gastos')->pluck('nombre');
foreach ($areas as $a) echo $a . PHP_EOL;
