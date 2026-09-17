<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.69;port=3306", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TODAS LAS BASES DE DATOS EN 192.168.22.69 ===\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '%poa%'");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Database (%poa%)'] . "\n";
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
