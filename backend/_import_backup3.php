<?php
$host = '192.168.22.238';
$user = 'root';
$pass = 'myPass1326!';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET GLOBAL max_allowed_packet = 1024 * 1024 * 512;");
    $pdo = null;

    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `bdMAR_alterna2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `bdMAR_alterna2`;");

    echo "Importando respaldo del 22 de sept...\n";
    $sqlFile = __DIR__ . '/backups/Respaldo-22Sept2026-20hrs.sql';
    
    // Conectar de nuevo con multi_statements
    $pdo = new PDO("mysql:host=$host;dbname=bdMAR_alterna2;charset=utf8mb4", $user, $pass, [
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "¡Respaldo importado correctamente en bdMAR_alterna2!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
