<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->prepare("SELECT count(*) FROM actividades_sustantivas WHERE proyecto_id = ?");
    $stmt->execute([1610]);
    echo "Acts for 1610: " . $stmt->fetchColumn() . "\n";
    
    $stmt->execute([1609]);
    echo "Acts for 1609: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
