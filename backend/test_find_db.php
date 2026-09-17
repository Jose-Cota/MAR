<?php
function findDatabases($host, $user, $pass) {
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]);
        $stmt = $pdo->query("SHOW DATABASES");
        $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Databases on $host:\n";
        print_r($dbs);
    } catch (PDOException $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}
findDatabases('192.168.22.167', 'sadpyrfdbu', 'cho9r=*&prIkLyi');
