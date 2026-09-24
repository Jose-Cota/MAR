<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->query("SELECT proyecto_id, nombre, descripcion FROM proyectos WHERE nombre LIKE '%Alineaci%' OR descripcion LIKE '%Alineaci%' LIMIT 5");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(count($proys) == 0) { echo "NOT FOUND IN ANY PROYECTO COLUMN\n"; }
    foreach ($proys as $p) {
        echo $p['proyecto_id'] . " | N: " . $p['nombre'] . " | D: " . substr($p['descripcion'], 0, 50) . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
