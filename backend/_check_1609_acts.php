<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT descripcion FROM actividades_sustantivas WHERE proyecto_id = 1609");
    $acts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($acts as $i => $a) {
        echo ($i+1) . " | " . substr($a, 0, 50) . "...\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
