<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbs = ["0201sadpyrf_mar2026", "0201sadpyrf_poa", "0201sadpyrf_poa2026"];

    foreach ($dbs as $db) {
        echo "=== DB: $db ===\n";
        $pdo->exec("USE `$db`");
        $stmt = $pdo->query("SHOW TABLES LIKE 'proyectos'");
        if ($stmt->rowCount() > 0) {
            $stmt2 = $pdo->query("SELECT COUNT(*) as c FROM proyectos WHERE ejercicio_id = 19");
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            echo "Proyectos 2027: " . $row['c'] . "\n";
        } else {
            echo "No proyectos table\n";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
