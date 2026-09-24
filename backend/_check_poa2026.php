<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM proyectos WHERE ejercicio_id = 19");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Proyectos 2027 in POA2026: " . $row['c'] . "\n";
    
    $stmt2 = $pdo->query("SELECT p.proyecto_id, p.nombre, p.responsable_operativo_id FROM proyectos p WHERE ejercicio_id = 19 LIMIT 5");
    while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo $row2['proyecto_id'] . " | " . $row2['responsable_operativo_id'] . " | " . $row2['nombre'] . "\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
