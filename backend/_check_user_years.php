<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("
        SELECT urg.ejercicio_id, COUNT(*) as count 
        FROM usuario_unidad_responsable uur
        JOIN unidades_responsables_gastos urg ON uur.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        GROUP BY urg.ejercicio_id
    ");
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($counts as $row) {
        echo "Ejercicio ID " . $row['ejercicio_id'] . ": " . $row['count'] . " asignaciones\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
