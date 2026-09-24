<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_poa`"); // Connection is 'poa_prod' in Laravel

    $stmt = $pdo->query("SELECT unidad_responsable_gasto_id, numero, nombre FROM unidades_responsables_gastos WHERE unidad_responsable_gasto_id < 240 AND nombre LIKE '%Presidencia%' LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->query("SELECT unidad_responsable_gasto_id, numero, nombre FROM unidades_responsables_gastos WHERE unidad_responsable_gasto_id < 240 AND nombre LIKE '%Controversias%' LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
