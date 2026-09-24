<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT proyecto_id, nombre, descripcion FROM proyectos WHERE ejercicio_id = 17 AND nombre LIKE '%Proyecto 2%'");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proys as $p) {
        $stmt2 = $pdo->prepare("SELECT count(*) FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt2->execute([$p['proyecto_id']]);
        echo "POA 2026 ID " . $p['proyecto_id'] . " | Acts: " . $stmt2->fetchColumn() . " | " . $p['nombre'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
