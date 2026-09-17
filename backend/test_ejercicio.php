<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT DISTINCT urg.ejercicio_id, ej.ejercicio FROM unidades_responsables_gastos urg LEFT JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "URG Ejercicio ID: " . $row['ejercicio_id'] . " | Año: " . $row['ejercicio'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
