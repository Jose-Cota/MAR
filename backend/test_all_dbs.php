<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TODAS LAS BASES DE DATOS VISIBLES ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Database'] . "\n";
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
