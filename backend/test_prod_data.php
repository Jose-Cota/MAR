<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.69;port=3306;dbname=0201sadpyrf_poa2026", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Ejercicios ===\n";
    $stmt = $pdo->query("SELECT * FROM ejercicios");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['ejercicio_id'] . " - " . $row['ejercicio'] . "\n";
    }

    echo "\n=== Proyectos Count ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM proyectos");
    echo "Proyectos: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
