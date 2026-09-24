<?php
$host = '192.168.22.238';
$db   = '0201sadpyrf_mar2026';
$user = 'root';
$pass = 'myPass1326!';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Incrementando max_allowed_packet...\n";
    $pdo->exec("SET GLOBAL max_allowed_packet = 1024 * 1024 * 512;"); // 512MB
    
    // Disconnect and reconnect to apply new max_allowed_packet for this session
    $pdo = null;

    $pdo = new PDO("mysql:host=$host;dbname=bdMAR_alterna;charset=utf8mb4", $user, $pass, [
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ejecutando SQL...\n";
    $sqlFile = __DIR__ . '/backups/bdMAR-23Sept2026-17hrs.sql';
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "¡Respaldo importado correctamente en bdMAR_alterna!\n";

} catch (PDOException $e) {
    echo "Error de DB: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
