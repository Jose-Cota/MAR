<?php
$columns = Schema::getColumnListing('unidades_responsables_gastos');
echo implode(', ', $columns) . PHP_EOL;
