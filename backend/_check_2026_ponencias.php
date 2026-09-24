<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("USE `0201sadpyrf_mar2026`");
    $stmt = $pdo->query("SELECT proyecto_id, nombre, responsable_operativo_id FROM proyectos WHERE ejercicio_id = 17 AND (responsable_operativo_id IN (SELECT responsable_operativo_id FROM responsables_operativos WHERE ejercicio_id = 17 AND unidad_responsable_gasto_id IN (SELECT unidad_responsable_gasto_id FROM unidades_responsables_gastos WHERE ejercicio_id = 17 AND nombre LIKE '%Ponencia%')))");
    $proys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($proys as $p) {
        $stmt2 = $pdo->prepare("SELECT count(*) as c FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt2->execute([$p['proyecto_id']]);
        echo $p['proyecto_id'] . " | MAR Actividades: " . $stmt2->fetchColumn() . " | " . $p['nombre'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
