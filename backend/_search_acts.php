<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->query("SELECT id, proyecto_id, descripcion FROM actividades_sustantivas WHERE descripcion LIKE '%Garantizar la autonom%' LIMIT 5");
    $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($acts);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
