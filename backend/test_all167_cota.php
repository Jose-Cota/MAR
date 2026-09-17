<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.167;port=3306", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $dbs = ['0201sadpyrf_poa', '0201sadpyrf_poa2025', '0202tedf_sadpyrf_poa'];
    
    foreach ($dbs as $db) {
        echo "=== Usuarios en $db con cota ===\n";
        try {
            $pdo->exec("USE $db");
            $stmt = $pdo->query("SELECT usuario_poa_id, usuario, password FROM usuarios_poa WHERE usuario LIKE '%cota%'");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                print_r($row);
            }
        } catch (Exception $ex) {}
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
