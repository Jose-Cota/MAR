<?php
$areas = DB::table('unidades_responsables_gastos')->where('nombre', 'LIKE', '%Ponencia%')->get();
foreach($areas as $a) echo $a->numero . ' - ' . $a->nombre . PHP_EOL;
