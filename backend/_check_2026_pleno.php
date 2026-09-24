<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("
        SELECT p.proyecto_id, p.nombre, p.ejercicio_id, count(a.id) as acts 
        FROM proyectos p 
        LEFT JOIN actividades_sustantivas a ON p.proyecto_id = a.proyecto_id 
        WHERE p.ejercicio_id = 17 AND p.nombre LIKE '%Acciones del Pleno%'
        GROUP BY p.proyecto_id
    ");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proys as $p) {
        echo $p['proyecto_id'] . " | Acts: " . $p['acts'] . " | " . $p['nombre'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
