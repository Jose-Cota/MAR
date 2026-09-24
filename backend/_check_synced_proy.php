<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT proyecto_id, responsable_operativo_id, nombre FROM proyectos WHERE ejercicio_id = 19 AND nombre LIKE '%Ponencia%'");
    $poaProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($poaProjects);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
