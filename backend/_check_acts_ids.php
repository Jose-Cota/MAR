<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->query("SELECT DISTINCT proyecto_id FROM actividades_sustantivas ORDER BY proyecto_id DESC LIMIT 20");
    $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($acts);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
