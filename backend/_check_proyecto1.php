<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT proyecto_id, nombre, ejercicio_id FROM proyectos WHERE nombre LIKE '%Proyecto 1%'");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proys as $p) {
        $stmt2 = $pdo->prepare("SELECT count(*) as c FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt2->execute([$p['proyecto_id']]);
        echo $p['proyecto_id'] . " | Ej: " . $p['ejercicio_id'] . " | Acts: " . $stmt2->fetchColumn() . " | " . $p['nombre'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
