<?php
$host = '192.168.22.69';
$user = 'root';
$pass = ''; // usually empty on xampp, or let's try root/root, but we will start with empty

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]);
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases on $host:\n";
    print_r($dbs);
} catch (PDOException $e) {
    echo "Failed with empty password: " . $e->getMessage() . "\n";
    try {
        $pass = 'root';
        $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]);
        $stmt = $pdo->query("SHOW DATABASES");
        $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Databases on $host with 'root' pass:\n";
        print_r($dbs);
    } catch (PDOException $e) {
        echo "Failed with 'root' password: " . $e->getMessage() . "\n";
    }
}
