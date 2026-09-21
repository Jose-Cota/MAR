<?php
$cols = DB::select('SHOW COLUMNS FROM unidades_responsables_gastos');
foreach ($cols as $c) echo $c->Field . ' ' . $c->Type . PHP_EOL;
