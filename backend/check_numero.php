<?php
$areas = DB::table('unidades_responsables_gastos')->select('numero', 'nombre')->get();
foreach($areas as $a) echo $a->numero . ' - ' . $a->nombre . PHP_EOL;
