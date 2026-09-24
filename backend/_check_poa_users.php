<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SHOW TABLES LIKE '%usuario%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in POA with 'usuario':\n";
    print_r($tables);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
