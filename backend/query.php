<?php
$pdo = new PDO('mysql:host=192.168.22.238;dbname=0201sadpyrf_mar2026;charset=utf8mb4', 'root', 'myPass1326!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id, name, email FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
