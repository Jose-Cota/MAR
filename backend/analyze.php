<?php
$tables = DB::connection('poa_prod')->select("
    SELECT TABLE_NAME, COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND (COLUMN_NAME LIKE '%ejercicio%' OR COLUMN_NAME LIKE '%anio%' OR COLUMN_NAME LIKE '%year%')
");
foreach($tables as $t) {
    echo $t->TABLE_NAME . ' -> ' . $t->COLUMN_NAME . "\n";
}
