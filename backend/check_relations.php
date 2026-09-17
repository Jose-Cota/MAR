<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- responsables_operativos ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM responsables_operativos");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "--- subprogramas ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM subprogramas");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "--- programas ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM programas");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "--- areas ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM areas");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
