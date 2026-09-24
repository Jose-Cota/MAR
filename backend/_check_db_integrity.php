<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    // 1. Usuarios con y sin UR
    $stmt = $pdo->query("SELECT count(*) FROM usuarios_poa");
    $total_users = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT count(DISTINCT usuario_poa_id) FROM usuario_unidad_responsable");
    $users_with_ur = $stmt->fetchColumn();

    echo "Usuarios totales: $total_users\n";
    echo "Usuarios con UR asignada: $users_with_ur\n";
    echo "Usuarios SIN UR asignada: " . ($total_users - $users_with_ur) . "\n\n";

    // 2. Proyectos con y sin RO
    $stmt = $pdo->query("SELECT count(*) FROM proyectos WHERE ejercicio_id IN (17, 19)");
    $total_proys = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT count(*) FROM proyectos WHERE (responsable_operativo_id IS NULL OR responsable_operativo_id = 0) AND ejercicio_id IN (17, 19)");
    $proys_null_ro = $stmt->fetchColumn();

    // Check if ROs exist
    $stmt = $pdo->query("SELECT count(p.proyecto_id) FROM proyectos p LEFT JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id WHERE ro.responsable_operativo_id IS NULL AND p.responsable_operativo_id IS NOT NULL AND p.ejercicio_id IN (17, 19)");
    $proys_invalid_ro = $stmt->fetchColumn();

    // Check if URs exist for ROs
    $stmt = $pdo->query("SELECT count(p.proyecto_id) FROM proyectos p JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id LEFT JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id WHERE urg.unidad_responsable_gasto_id IS NULL AND p.ejercicio_id IN (17, 19)");
    $proys_invalid_urg = $stmt->fetchColumn();

    echo "Proyectos totales (2026 y 2027): $total_proys\n";
    echo "Proyectos sin RO asignado: $proys_null_ro\n";
    echo "Proyectos con RO inválido/borrado: $proys_invalid_ro\n";
    echo "Proyectos donde la UR del RO no existe: $proys_invalid_urg\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
