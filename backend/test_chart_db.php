<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "
        SELECT DISTINCT py.numero as py_numero
        FROM proyectos as py
    ";
    echo "PY Distinct count: " . $pdo->query("SELECT COUNT(*) FROM ($query) as t")->fetchColumn() . "\n";
    
    $query = "
        SELECT DISTINCT urg.numero as urg_numero
        FROM unidades_responsables_gastos as urg
    ";
    echo "URG Distinct count: " . $pdo->query("SELECT COUNT(*) FROM ($query) as t")->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
