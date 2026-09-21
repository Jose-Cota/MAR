<?php
$ids = DB::table('riesgos')->distinct()->pluck('ejercicio_id')->toArray();
echo 'ejercicio_ids in riesgos: ' . implode(', ', $ids) . PHP_EOL;
