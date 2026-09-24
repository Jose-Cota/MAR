<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("
        SELECT a.area_id, a.nombre as area_nombre, urg.numero, urg.nombre as urg_nombre 
        FROM areas a 
        LEFT JOIN `0201sadpyrf_poa`.unidades_responsables_gastos urg 
        ON (a.nombre = urg.nombre OR a.nombre LIKE CONCAT('%', urg.nombre, '%') OR urg.nombre LIKE CONCAT('%', a.nombre, '%'))
        WHERE urg.unidad_responsable_gasto_id < 240
        GROUP BY a.area_id
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
