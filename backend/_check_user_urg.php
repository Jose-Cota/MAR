<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SHOW COLUMNS FROM usuario_unidad_responsable");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in usuario_unidad_responsable:\n";
    foreach ($columns as $c) {
        echo $c['Field'] . "\n";
    }

    $stmt2 = $pdo->query("SELECT count(*) as c FROM usuario_unidad_responsable");
    echo "Total rows: " . $stmt2->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
