<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("SELECT area_id, nombre FROM areas WHERE nombre LIKE '%Controversias%'");
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($areas as $a) {
        $stmt2 = $pdo->prepare("SELECT count(*) FROM riesgos WHERE area_id = ? AND ejercicio_id = 17");
        $stmt2->execute([$a['area_id']]);
        echo "Area ID: " . $a['area_id'] . " | " . $a['nombre'] . " | Riesgos: " . $stmt2->fetchColumn() . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
