<?php
$db = new PDO('sqlite:database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get schema of proyectos table
$stmt = $db->query("PRAGMA table_info(proyectos)");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in proyectos:\n";
foreach ($cols as $col) {
    echo $col['name'] . "\n";
}

// Check how many projects in 2025 vs 2026
$stmt2 = $db->query("SELECT ejercicio, COUNT(*) as c FROM proyectos GROUP BY ejercicio");
$counts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\nProject counts by ejercicio:\n";
print_r($counts);

// Get projects for 2025 and 2026
$stmt3 = $db->query("SELECT proyecto_id, numero, nombre, ejercicio FROM proyectos WHERE ejercicio IN (2025, 2026) ORDER BY ejercicio, CAST(numero AS INTEGER)");
$projs = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "\nProjects:\n";
foreach ($projs as $p) {
    echo "{$p['ejercicio']} | PY: {$p['numero']} | ID: {$p['proyecto_id']} | {$p['nombre']}\n";
}
