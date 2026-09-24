<?php
$host = '192.168.22.238';
$db   = '0201sadpyrf_mar2026';
$user = 'root';
$pass = 'myPass1326!';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conectado. Creando base de datos bdMAR_alterna...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `bdMAR_alterna` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `bdMAR_alterna`;");

    echo "Base de datos seleccionada. Leyendo respaldo...\n";
    $sqlFile = __DIR__ . '/backups/bdMAR-23Sept2026-17hrs.sql';
    
    if (!file_exists($sqlFile)) {
        die("El archivo $sqlFile no existe.\n");
    }

    // Since the file is 37MB, we'll try to execute it directly. If it fails due to memory, we'll stream it.
    // However, PDO doesn't always support multiple statements by default in all configurations unless allowed.
    // Let's connect with multi-statements enabled.
    $pdo = new PDO("mysql:host=$host;dbname=bdMAR_alterna;charset=utf8mb4", $user, $pass, [
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ejecutando SQL...\n";
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "¡Respaldo importado correctamente en bdMAR_alterna!\n";

} catch (PDOException $e) {
    echo "Error de DB: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
