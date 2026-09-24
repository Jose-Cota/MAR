<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT ejercicio_id FROM proyectos WHERE proyecto_id = 1610");
    echo "Ejercicio for 1610: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
