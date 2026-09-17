<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.167;port=3306;dbname=0201sadpyrf_poa2025", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 5 Usuarios en PROD .167 ===\n";
    $stmt = $pdo->query("SELECT usuario, password FROM usuarios_poa LIMIT 5");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
