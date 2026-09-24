<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT * FROM proyectos WHERE proyecto_id = 1423");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        print_r($row);
    } else {
        echo "NOT FOUND";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
