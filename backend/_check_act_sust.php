<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->query("SELECT proyecto_id, nombre FROM proyectos WHERE ejercicio_id = 19 AND nombre LIKE '%Proyecto 2%'");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proys as $p) {
        echo "PROYECTO ID: " . $p['proyecto_id'] . " | " . $p['nombre'] . "\n";
        
        $stmt2 = $pdo->prepare("SELECT count(*) as c FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt2->execute([$p['proyecto_id']]);
        echo "  Actividades MAR: " . $stmt2->fetchColumn() . "\n";
    }

    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT proyecto_id, nombre FROM proyectos WHERE ejercicio_id = 19 AND nombre LIKE '%Proyecto 2%'");
    $proysPoa = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proysPoa as $p) {
        echo "POA PROYECTO ID: " . $p['proyecto_id'] . " | " . $p['nombre'] . "\n";
        
        $stmt2 = $pdo->prepare("SELECT count(*) as c FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt2->execute([$p['proyecto_id']]);
        echo "  Actividades POA: " . $stmt2->fetchColumn() . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
