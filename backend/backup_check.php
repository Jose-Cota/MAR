<?php
$host = '192.168.22.238';
$port = '3306';
$db   = '0201sadpyrf_mar2026';
$user = 'root';
$pass = 'myPass1326!';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener IDs de ejercicios 2026 y 2027
$stmt = $pdo->query("SELECT ejercicio_id, ejercicio FROM ejercicios WHERE ejercicio IN (2026, 2027)");
$ejercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($ejercicios)) {
    echo "No se encontraron ejercicios 2026 o 2027.\n";
    // Mostrar los disponibles
    $all = $pdo->query("SELECT ejercicio_id, ejercicio FROM ejercicios ORDER BY ejercicio DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "Ejercicios disponibles:\n";
    foreach ($all as $e) echo "  {$e['ejercicio']} => id={$e['ejercicio_id']}\n";
    exit(1);
}

foreach ($ejercicios as $e) {
    echo "Encontrado: ejercicio={$e['ejercicio']} id={$e['ejercicio_id']}\n";
}

// Obtener todos los IDs de ejercicio a respaldar
$idsEjercicio = array_column($ejercicios, 'ejercicio_id');
$placeholders = implode(',', $idsEjercicio);

// Contar riesgos
$count = $pdo->query("SELECT COUNT(*) FROM riesgos WHERE ejercicio_id IN ($placeholders)")->fetchColumn();
echo "Total riesgos a respaldar: $count\n";
