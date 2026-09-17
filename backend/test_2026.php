<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== proyectos ===\n";
    $stmt = $pdo->query("DESCRIBE proyectos");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }

    echo "=== ejercicios ===\n";
    $stmt = $pdo->query("SELECT * FROM ejercicios");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['ejercicio_id'] . " - " . $row['ejercicio'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
