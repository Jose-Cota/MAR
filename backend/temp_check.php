<?php
$areas = DB::table('unidades_responsables_gastos')->get();
$dbAreaMap = []; // Name => urg_id
foreach ($areas as $a) {
    $name = trim($a->denominacion);
    if (strpos(strtolower($name), 'pleno') !== false) {
        $name = 'Pleno';
    }
    echo strtolower($name) . " => " . $a->unidad_responsable_gasto_id . "\n";
}
