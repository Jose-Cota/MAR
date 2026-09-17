<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.69;port=3306;dbname=0201sadpyrf_poa2026", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Usuarios con cota en PROD ===\n";
    $stmt = $pdo->prepare("SELECT usuario_poa_id, usuario, password FROM usuarios_poa WHERE usuario LIKE '%cota%'");
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error PROD: " . $e->getMessage() . "\n";
}
