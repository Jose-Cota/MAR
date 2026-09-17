<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Proyectos count: " . $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn() . "\n";
    echo "Subprogramas count: " . $pdo->query("SELECT COUNT(*) FROM subprogramas")->fetchColumn() . "\n";
    echo "Programas count: " . $pdo->query("SELECT COUNT(*) FROM programas")->fetchColumn() . "\n";
    
    $query = "
        SELECT py.proyecto_id
        FROM proyectos as py
        JOIN subprogramas as sp ON py.subprograma_id = sp.subprograma_id
        JOIN programas as pg ON sp.programa_id = pg.programa_id
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
    ";
    echo "Join count: " . $pdo->query("SELECT COUNT(*) FROM ($query) as t")->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
