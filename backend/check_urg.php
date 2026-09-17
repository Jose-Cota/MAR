<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SHOW COLUMNS FROM unidades_responsables_gasto");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
