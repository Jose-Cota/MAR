<?php
$tables = ['proyectos', 'subprogramas', 'responsables_operativos'];
foreach($tables as $t) {
    $cols = DB::connection('poa_prod')->select("SHOW COLUMNS FROM {$t}");
    echo "Table: {$t}\n";
    foreach($cols as $c) {
        if (strpos($c->Field, 'ejercicio') !== false || strpos($c->Field, 'anio') !== false || strpos($c->Field, 'year') !== false) {
            echo "  - {$c->Field}\n";
        }
    }
}
