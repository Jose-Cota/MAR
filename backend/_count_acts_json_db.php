<?php
$json = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($json, true);

$jsonCounts = [];
foreach ($data['poaActions'] as $action) {
    if (strpos($action['id'], '-2026-') !== false) {
        // e.g. PRES-2026-A1
        $parts = explode('-', $action['id']);
        $area = $parts[0];
        if (!isset($jsonCounts[$area])) $jsonCounts[$area] = 0;
        $jsonCounts[$area]++;
    }
}

echo "=== JSON COUNTS 2026 ===\n";
foreach ($jsonCounts as $area => $count) {
    echo "$area: $count\n";
}

try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $stmt = $pdo->query("
        SELECT urg.nombre, COUNT(a.id) as count
        FROM actividades_sustantivas a
        JOIN proyectos p ON a.proyecto_id = p.proyecto_id
        JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        WHERE p.ejercicio_id = 17
        GROUP BY urg.nombre
    ");
    echo "\n=== DB COUNTS 2026 ===\n";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row['nombre'] . ": " . $row['count'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
