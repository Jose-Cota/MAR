<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Get correct mappings from poa2026
    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT nombre, responsable_operativo_id FROM proyectos WHERE ejercicio_id = 19");
    $poaProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Update mar2026
    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $updates = 0;
    foreach ($poaProjects as $proj) {
        $updateStmt = $pdo->prepare("UPDATE proyectos SET responsable_operativo_id = :ro_id WHERE ejercicio_id = 19 AND nombre = :nombre");
        $updateStmt->execute([
            ':ro_id' => $proj['responsable_operativo_id'],
            ':nombre' => $proj['nombre']
        ]);
        $updates += $updateStmt->rowCount();
    }
    echo "Updated $updates projects with correct ROs in mar2026.\n";

    // 3. Sync usuario_unidad_responsable (Truncate and copy)
    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT * FROM usuario_unidad_responsable");
    $userUrg = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $pdo->exec("TRUNCATE TABLE usuario_unidad_responsable");
    if (count($userUrg) > 0) {
        $columns = array_keys($userUrg[0]);
        $columnsList = implode(", ", $columns);
        $placeholders = implode(", ", array_fill(0, count($columns), "?"));
        
        $insertStmt = $pdo->prepare("INSERT INTO usuario_unidad_responsable ($columnsList) VALUES ($placeholders)");
        foreach ($userUrg as $row) {
            $insertStmt->execute(array_values($row));
        }
    }
    echo "Synced " . count($userUrg) . " rows into usuario_unidad_responsable in mar2026.\n";

    // 4. Sync usuarios_responsables_operativos (Truncate and copy)
    $pdo->exec("USE `0201sadpyrf_poa2026`");
    $stmt = $pdo->query("SELECT * FROM usuarios_responsables_operativos");
    $userRo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $pdo->exec("TRUNCATE TABLE usuarios_responsables_operativos");
    if (count($userRo) > 0) {
        $columns = array_keys($userRo[0]);
        $columnsList = implode(", ", $columns);
        $placeholders = implode(", ", array_fill(0, count($columns), "?"));
        
        $insertStmt = $pdo->prepare("INSERT INTO usuarios_responsables_operativos ($columnsList) VALUES ($placeholders)");
        foreach ($userRo as $row) {
            $insertStmt->execute(array_values($row));
        }
    }
    echo "Synced " . count($userRo) . " rows into usuarios_responsables_operativos in mar2026.\n";

    // 5. Sync usuarios_poa? The user only asked for "usuario esta asignado a una UR"
    // which is covered by usuario_unidad_responsable and usuarios_responsables_operativos.

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
