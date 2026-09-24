<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT count(*) FROM riesgos WHERE area_id = 1 AND ejercicio_id = 17");
    echo "Riesgos for Presidencia (area 1) in 2026: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
